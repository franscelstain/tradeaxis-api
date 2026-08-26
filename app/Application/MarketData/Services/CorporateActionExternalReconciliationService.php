<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bidirectional reconciliation of recorded, verified corporate actions against an exchange/CSD
 * corpus. An incomplete external scope is useful evidence, but it never qualifies a period as
 * action-complete.
 */
class CorporateActionExternalReconciliationService
{
    const MANIFEST_SCHEMA = 'market-data-authoritative-corporate-action-corpus/v1';
    private const AUTHORITY_CLASSES = ['EXCHANGE', 'CSD'];

    public function reconcileFile($manifestPath, $apply = false): array
    {
        if (! is_file($manifestPath)) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_MANIFEST_NOT_FOUND: '.$manifestPath);
        }
        $raw = file_get_contents($manifestPath);
        if ($raw === false) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_MANIFEST_NOT_READABLE: '.$manifestPath);
        }

        // Windows PowerShell 5.1 `Set-Content -Encoding UTF8` writes an UTF-8 BOM. The BOM is an
        // encoding marker, not manifest semantics, so strip it for JSON decoding while retaining
        // the exact raw bytes for the evidence hash.
        $json = $this->stripUtf8Bom($raw);
        $manifest = json_decode($json, true);
        if (! is_array($manifest)) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_MANIFEST_JSON_INVALID.');
        }

        return $this->reconcileManifest($manifest, hash('sha256', $raw), (bool) $apply);
    }

    private function stripUtf8Bom(string $raw): string
    {
        return substr($raw, 0, 3) === "\xEF\xBB\xBF" ? substr($raw, 3) : $raw;
    }

    public function reconcileManifest(array $manifest, $manifestSha256 = null, $apply = false): array
    {
        $this->assertFoundation();
        $normalized = $this->normalizeManifest($manifest);
        $manifestSha256 = $manifestSha256 ?: hash('sha256', json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $platform = $this->platformEvents($normalized['scope_start'], $normalized['scope_end'], $normalized['action_types']);
        $authority = [];
        foreach ($normalized['events'] as $event) {
            $authority[$this->eventKey($event)] = $event;
        }
        $platformByKey = [];
        foreach ($platform as $event) {
            $platformByKey[$this->eventKey($event)] = $event;
        }

        $missing = [];
        $unexpected = [];
        $mismatched = [];

        foreach ($authority as $key => $event) {
            if (! isset($platformByKey[$key])) {
                $missing[] = $event;
                continue;
            }
            $actual = $platformByKey[$key];
            if ((string) $event['terms_sha256'] !== (string) $actual['terms_sha256']) {
                $mismatched[] = ['authority' => $event, 'platform' => $actual];
            }
        }
        foreach ($platformByKey as $key => $event) {
            if (! isset($authority[$key])) {
                $unexpected[] = $event;
            }
        }

        $mismatchCount = count($mismatched);
        if (! $normalized['scope_complete']) {
            $state = 'AUTHORITY_SCOPE_INCOMPLETE';
        } elseif (count($missing) === 0 && count($unexpected) === 0 && $mismatchCount === 0) {
            $state = 'PASS';
        } else {
            $state = 'FAIL';
        }

        $details = [
            'missing_platform' => $missing,
            'unexpected_platform' => $unexpected,
            'term_mismatches' => $mismatched,
            'qualification' => $normalized['scope_complete']
                ? 'FULL_SCOPE_CHECKED'
                : 'PERIOD_NOT_ACTION_COMPLETE',
        ];
        $uid = hash('sha256', json_encode([
            'schema' => self::MANIFEST_SCHEMA,
            'authority_name' => $normalized['authority_name'],
            'authority_class' => $normalized['authority_class'],
            'scope_start' => $normalized['scope_start'],
            'scope_end' => $normalized['scope_end'],
            'scope_complete' => $normalized['scope_complete'],
            'manifest_sha256' => $manifestSha256,
            'state' => $state,
            'missing' => count($missing),
            'unexpected' => count($unexpected),
            'mismatch' => $mismatchCount,
        ], JSON_UNESCAPED_SLASHES));

        $result = [
            'reconciliation_uid' => $uid,
            'manifest_schema' => self::MANIFEST_SCHEMA,
            'authority_name' => $normalized['authority_name'],
            'authority_class' => $normalized['authority_class'],
            'scope_start' => $normalized['scope_start'],
            'scope_end' => $normalized['scope_end'],
            'scope_complete' => $normalized['scope_complete'],
            'manifest_sha256' => $manifestSha256,
            'manifest_event_count' => count($authority),
            'platform_event_count' => count($platformByKey),
            'missing_platform_count' => count($missing),
            'unexpected_platform_count' => count($unexpected),
            'mismatch_count' => $mismatchCount,
            'reconciliation_state' => $state,
            'details' => $details,
            'persisted' => false,
        ];

        if ($apply) {
            $result['persisted'] = $this->persist($result);
        }

        return $result;
    }

    private function normalizeManifest(array $manifest): array
    {
        if ((string) ($manifest['schema_version'] ?? '') !== self::MANIFEST_SCHEMA) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_MANIFEST_SCHEMA_INVALID.');
        }
        $authorityName = trim((string) ($manifest['authority_name'] ?? ''));
        $authorityClass = strtoupper(trim((string) ($manifest['authority_class'] ?? '')));
        if ($authorityName === '' || ! in_array($authorityClass, self::AUTHORITY_CLASSES, true)) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_IDENTITY_INVALID.');
        }
        $scopeStart = Carbon::parse((string) ($manifest['scope_start'] ?? ''))->toDateString();
        $scopeEnd = Carbon::parse((string) ($manifest['scope_end'] ?? ''))->toDateString();
        if ($scopeStart > $scopeEnd) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_SCOPE_INVALID.');
        }
        $scopeComplete = (bool) ($manifest['scope_complete'] ?? false);
        if ($scopeComplete && $scopeStart !== MarketDataScope::DATASET_START) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_COMPLETE_SCOPE_MUST_START_AT_DATASET_START.');
        }
        $actionTypes = array_values(array_unique(array_map(function ($value) {
            return strtoupper(trim((string) $value));
        }, (array) ($manifest['action_types'] ?? []))));
        $actionTypes = array_values(array_filter($actionTypes));
        if ($actionTypes === []) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_ACTION_TYPES_REQUIRED.');
        }

        $events = [];
        foreach ((array) ($manifest['events'] ?? []) as $index => $event) {
            if (! is_array($event)) {
                throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_EVENT_INVALID: '.$index);
            }
            $listingUid = trim((string) ($event['listing_uid'] ?? ''));
            $type = strtoupper(trim((string) ($event['action_type_code'] ?? '')));
            $exDate = isset($event['ex_date']) && $event['ex_date'] !== null && $event['ex_date'] !== ''
                ? Carbon::parse((string) $event['ex_date'])->toDateString()
                : null;
            $termsSha = strtolower(trim((string) ($event['terms_sha256'] ?? '')));
            if ($listingUid === '' || $type === '' || $exDate === null || ! preg_match('/^[a-f0-9]{64}$/', $termsSha)) {
                throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_EVENT_IDENTITY_INCOMPLETE: '.$index);
            }
            if (! in_array($type, $actionTypes, true) || $exDate < $scopeStart || $exDate > $scopeEnd) {
                throw new \InvalidArgumentException('CORPORATE_ACTION_AUTHORITY_EVENT_OUTSIDE_SCOPE: '.$index);
            }
            $events[] = [
                'listing_uid' => $listingUid,
                'action_type_code' => $type,
                'ex_date' => $exDate,
                'terms_sha256' => $termsSha,
            ];
        }

        return compact('authorityName', 'authorityClass') + [
            'authority_name' => $authorityName,
            'authority_class' => $authorityClass,
            'scope_start' => $scopeStart,
            'scope_end' => $scopeEnd,
            'scope_complete' => $scopeComplete,
            'action_types' => $actionTypes,
            'events' => $events,
        ];
    }

    private function platformEvents($scopeStart, $scopeEnd, array $actionTypes): array
    {
        $rows = DB::table('md_corporate_action_revisions as revision')
            ->join('md_listings as listing', 'listing.listing_id', '=', 'revision.listing_id')
            ->leftJoin('md_corporate_action_revisions as newer', 'newer.supersedes_revision_id', '=', 'revision.corporate_action_revision_id')
            ->whereNull('newer.corporate_action_revision_id')
            ->whereIn('revision.verification_state', ['AUTHORITATIVE_VERIFIED', 'MANUAL_VERIFIED'])
            ->where('revision.lifecycle_state', 'EFFECTIVE')
            ->whereIn('revision.action_type_code', $actionTypes)
            ->whereNotNull('revision.ex_date')
            ->whereBetween('revision.ex_date', [$scopeStart, $scopeEnd])
            ->orderBy('listing.listing_uid')
            ->orderBy('revision.action_type_code')
            ->orderBy('revision.ex_date')
            ->get(['listing.listing_uid', 'revision.action_type_code', 'revision.ex_date', 'revision.terms_json']);

        $out = [];
        foreach ($rows as $row) {
            $terms = json_decode((string) $row->terms_json, true);
            if (! is_array($terms)) {
                $terms = [];
            }
            $terms = $this->sortRecursive($terms);
            $out[] = [
                'listing_uid' => (string) $row->listing_uid,
                'action_type_code' => (string) $row->action_type_code,
                'ex_date' => (string) $row->ex_date,
                'terms_sha256' => hash('sha256', json_encode($terms, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            ];
        }

        return $out;
    }

    private function persist(array $result): bool
    {
        $existing = DB::table('md_corporate_action_reconciliations')
            ->where('reconciliation_uid', $result['reconciliation_uid'])->first();
        if ($existing) {
            return false;
        }
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        DB::table('md_corporate_action_reconciliations')->insert([
            'reconciliation_uid' => $result['reconciliation_uid'],
            'scope_start' => $result['scope_start'],
            'scope_end' => $result['scope_end'],
            'authority_name' => $result['authority_name'],
            'authority_class' => $result['authority_class'],
            'scope_complete' => $result['scope_complete'] ? 1 : 0,
            'manifest_sha256' => $result['manifest_sha256'],
            'manifest_event_count' => $result['manifest_event_count'],
            'platform_event_count' => $result['platform_event_count'],
            'missing_platform_count' => $result['missing_platform_count'],
            'unexpected_platform_count' => $result['unexpected_platform_count'],
            'mismatch_count' => $result['mismatch_count'],
            'reconciliation_state' => $result['reconciliation_state'],
            'details_json' => json_encode($result['details'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'recorded_at' => $now,
            'created_at' => $now,
        ]);

        return true;
    }

    private function eventKey(array $event): string
    {
        return $event['listing_uid'].'|'.$event['action_type_code'].'|'.$event['ex_date'];
    }

    private function sortRecursive(array $value): array
    {
        foreach ($value as $key => $child) {
            if (is_array($child)) {
                $value[$key] = $this->sortRecursive($child);
            }
        }
        if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
            ksort($value, SORT_STRING);
        }

        return $value;
    }

    private function assertFoundation(): void
    {
        foreach (['md_corporate_action_revisions', 'md_listings', 'md_corporate_action_reconciliations'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException('CORPORATE_ACTION_RECONCILIATION_FOUNDATION_MISSING: '.$table);
            }
        }
    }
}
