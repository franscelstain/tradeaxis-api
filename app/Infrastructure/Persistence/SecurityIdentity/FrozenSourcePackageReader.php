<?php

namespace App\Infrastructure\Persistence\SecurityIdentity;

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;

/** Filesystem admission boundary for the explicitly fingerprinted foundation packages. No network fetch. */
final class FrozenSourcePackageReader
{
    public const VERSION = 'foundation-source-basis-20260928-v1';
    public const MANIFEST_HASH = 'ffa6acc11d41a2e219719dc035d381844983407210a3d2c39e5c9e52c7f2f45b';
    public const ASSIGNMENTS_HASH = 'dc86d2a9453b3edaaa32736e4ea5022152916bd97c62c13a84c8e4b29953b4aa';
    public const IKPM_LISTING_VERSION = 'foundation-source-basis-20260929-ikpm-listing-v2';
    public const IKPM_LISTING_MANIFEST_HASH = '9b31ec1a99e0ca18d2b82ef13e69d5bae872b7f75021d9ee1e4cf8ddcdafe47c';
    public const IKPM_LISTING_ASSIGNMENTS_HASH = 'df8783af5f2cc4c463686db73e2cd64bc45281798516408b3741e0313b30b20f';
    public const IKPM_CURRENT_VERSION = 'foundation-source-basis-20260929-ikpm-current-window-v3';
    public const IKPM_CURRENT_MANIFEST_HASH = 'f85c4ba3f0606177e58c3b94e2e7593819466df9c8cba238272cd1305405ac4f';
    public const IKPM_CURRENT_ASSIGNMENTS_HASH = 'c3d9206fdb6d77e21264cccf279fe23d1ac8a790a0e8db8232c8018a784f2818';

    public function read(string $directory, string $assignmentPath): FoundationRegistry
    {
        $manifestBytes = $this->bytes($directory.'/manifest.json');
        $manifest = $this->json($manifestBytes);
        if (($manifest['package_version'] ?? null) === self::IKPM_CURRENT_VERSION) {
            return $this->readIkpmCurrentWindow($directory, $assignmentPath, $manifestBytes, $manifest);
        }
        if (($manifest['package_version'] ?? null) === self::IKPM_LISTING_VERSION) {
            return $this->readIkpmListing($directory, $assignmentPath, $manifestBytes, $manifest);
        }
        if (($manifest['package_version'] ?? null) !== self::VERSION || ($manifest['manifest_version'] ?? null) !== 'foundation_source_basis_manifest_v1') {
            throw new \DomainException('SOURCE_PACKAGE_VERSION_UNSUPPORTED');
        }
        if (hash('sha256', $manifestBytes) !== self::MANIFEST_HASH) { throw new \DomainException('SOURCE_MANIFEST_FINGERPRINT_MISMATCH'); }
        $members = [];
        foreach ($manifest['files'] as $member) {
            $path = $member['path'];
            if (basename($path) !== $path || isset($members[$path])) { throw new \DomainException('SOURCE_MEMBER_PATH_INVALID'); }
            $bytes = $this->bytes($directory.'/'.$path);
            if (strlen($bytes) !== $member['bytes'] || hash('sha256', $bytes) !== $member['sha256']) { throw new \DomainException('SOURCE_MEMBER_FINGERPRINT_MISMATCH:'.$path); }
            $members[$path] = $this->json($bytes);
        }
        $assignments = $this->json($this->verifiedBytes($assignmentPath, self::ASSIGNMENTS_HASH));
        if ($assignments['assignment_version'] !== 'security-identity-bootstrap-assignments/v1' || $assignments['source_manifest_sha256'] !== self::MANIFEST_HASH || $assignments['basis'] !== 'E-MD-B10-A002-005') {
            throw new \DomainException('RETAINED_ASSIGNMENT_BINDING_INVALID');
        }
        $admitted = $members['admitted_facts.json'];
        if ($admitted['admission_status'] !== 'ADMITTED_BOUNDED_SOURCE_FACTS_ONLY') { throw new \DomainException('SOURCE_ADMISSION_REQUIRED'); }
        $at = $this->utc($admitted['admitted_at']);
        $sources = [];
        $entities = [];
        $revisions = [];
        foreach ($assignments['records'] as $assignment) {
            $found = array_values(array_filter($admitted['records'], static fn (array $row): bool => $row['source_record'] === $assignment['source_record']));
            if (count($found) !== 1) { throw new \DomainException('SOURCE_RECORD_BINDING_INVALID'); }
            $record = $found[0];
            $isKsei = isset($assignment['instrument_id']);
            $extract = $members[$isKsei ? 'ksei_registered_security_extract.json' : 'eipo_offering_extract.json'];
            if ($extract['source_url'] !== $record['source_record'] || $extract['source_field_values'] !== $record['facts']) { throw new \DomainException('SOURCE_EXTRACTION_MISMATCH'); }
            $fields = $isKsei ? ['issuer', 'security_name', 'isin', 'short_code', 'type', 'currency'] : ['issuer_name'];
            $sources[$record['source_record']] = [
                'facts' => $record['facts'], 'admitted_fields' => $fields,
                'evidence_class' => 'BOUNDED_FACT', 'source_revision' => null,
                'source_known_at' => null, 'captured_at' => $this->utc($record['known_to_this_package_at']),
                'review_reference' => 'E-MD-B10-A002-005', 'capture_metadata' => $extract,
                'admission_limits' => $record['facts_admissible_for'],
            ];
            $entities[] = $this->entity($assignment['issuer_id'], 'ISSUER', null, $record['source_record']);
            $nameKey = $isKsei ? 'issuer' : 'issuer_name';
            $revisions[] = $this->profile($assignment['issuer_profile_revision_id'], $assignment['issuer_id'], $record['source_record'], [$nameKey => $record['facts'][$nameKey]], $at);
            if ($isKsei) {
                $entities[] = $this->entity($assignment['instrument_id'], 'INSTRUMENT', $assignment['issuer_id'], $record['source_record']);
                $data = array_intersect_key($record['facts'], array_flip(['security_name', 'isin', 'type', 'currency', 'short_code']));
                $revisions[] = $this->profile($assignment['instrument_profile_revision_id'], $assignment['instrument_id'], $record['source_record'], $data, $at);
            }
        }
        $holds = [];
        foreach ($members['unresolved.json']['rows'] as $row) {
            $holds[] = ['hold_hash' => hash('sha256', self::MANIFEST_HASH.'|'.$row['scope']), 'package_hash' => self::MANIFEST_HASH, 'scope' => $row['scope'], 'held_facts' => $row['held_facts']];
        }
        return new FoundationRegistry([
            'registry_version' => 'security-identity-registry/v1',
            'packages' => [[
                'package_hash' => self::MANIFEST_HASH, 'package_version' => self::VERSION,
                'recorded_at' => $at, 'admission_evidence' => 'E-MD-B10-A002-005',
                'sources' => $sources, 'manifest' => $manifest, 'assignment_sha256' => self::ASSIGNMENTS_HASH,
                'bounded_source_records' => $admitted['records'],
            ]],
            'entities' => $entities, 'revisions' => $revisions, 'holds' => $holds,
        ]);
    }

    private function readIkpmListing(string $directory, string $assignmentPath, string $manifestBytes, array $manifest): FoundationRegistry
    {
        if (($manifest['manifest_version'] ?? null) !== 'foundation_source_basis_manifest_v1') {
            throw new \DomainException('SOURCE_PACKAGE_VERSION_UNSUPPORTED');
        }
        if (hash('sha256', $manifestBytes) !== self::IKPM_LISTING_MANIFEST_HASH) {
            throw new \DomainException('SOURCE_MANIFEST_FINGERPRINT_MISMATCH');
        }
        if (($manifest['admission']['predecessor_manifest_sha256'] ?? null) !== self::MANIFEST_HASH
            || ($manifest['admission']['no_predecessor_rewrite'] ?? null) !== true) {
            throw new \DomainException('SOURCE_PREDECESSOR_BINDING_INVALID');
        }

        $members = [];
        foreach ($manifest['files'] ?? [] as $member) {
            $path = $member['path'] ?? null;
            if (!is_string($path) || basename($path) !== $path || isset($members[$path])) {
                throw new \DomainException('SOURCE_MEMBER_PATH_INVALID');
            }
            $bytes = $this->bytes($directory.'/'.$path);
            if (strlen($bytes) !== ($member['bytes'] ?? null) || hash('sha256', $bytes) !== ($member['sha256'] ?? null)) {
                throw new \DomainException('SOURCE_MEMBER_FINGERPRINT_MISMATCH:'.$path);
            }
            $members[$path] = $this->json($bytes);
        }
        foreach (['acquisition_manifest.json', 'admitted_facts.json', 'idx_listing_announcement_extract.json', 'predecessor_binding.json', 'unresolved.json', 'yahoo_chart_extract.json', 'yahoo_chart_response.json'] as $required) {
            if (!isset($members[$required])) { throw new \DomainException('SOURCE_MEMBER_MISSING:'.$required); }
        }

        $assignments = $this->json($this->verifiedBytes($assignmentPath, self::IKPM_LISTING_ASSIGNMENTS_HASH));
        if (($assignments['assignment_version'] ?? null) !== 'security-identity-ikpm-listing-assignments/v1'
            || ($assignments['source_manifest_sha256'] ?? null) !== self::IKPM_LISTING_MANIFEST_HASH
            || ($assignments['predecessor_manifest_sha256'] ?? null) !== self::MANIFEST_HASH
            || ($assignments['basis'] ?? null) !== 'E-MD-B10-A002-007'
            || ($assignments['retained_issuer_id'] ?? null) !== '35ff26e0-a043-41c8-8c6e-f7f2d4227214'
            || ($assignments['retained_instrument_id'] ?? null) !== '93453e6d-87b4-4131-8379-a91fb1766fac') {
            throw new \DomainException('RETAINED_ASSIGNMENT_BINDING_INVALID');
        }

        $admitted = $members['admitted_facts.json'];
        if (($admitted['admission_status'] ?? null) !== 'ADMITTED_BOUNDED_IKPM_LISTING_FACTS'
            || ($admitted['admission_evidence'] ?? null) !== 'E-MD-B10-A002-007') {
            throw new \DomainException('SOURCE_ADMISSION_REQUIRED');
        }
        $records = $admitted['records'] ?? [];
        if (count($records) !== 2) { throw new \DomainException('SOURCE_RECORD_BINDING_INVALID'); }
        $records = array_column($records, null, 'source_record');
        // Source locators are provenance carried by the fingerprinted package (manifest hash checked
        // above), not request shape written into persistence code; provider transport stays in the
        // source adapter. Each extract names its own record and the acquisition manifest must agree.
        $idxUrl = $members['idx_listing_announcement_extract.json']['source_url'] ?? null;
        $yahooUrl = $members['yahoo_chart_extract.json']['source_url'] ?? null;
        $acquired = array_column($members['acquisition_manifest.json']['sources'] ?? [], 'locator');
        sort($acquired);
        $extracted = [$idxUrl, $yahooUrl];
        sort($extracted);
        if (!is_string($idxUrl) || !is_string($yahooUrl) || $idxUrl === $yahooUrl || $acquired !== $extracted
            || !isset($records[$idxUrl], $records[$yahooUrl])) {
            throw new \DomainException('SOURCE_RECORD_BINDING_INVALID');
        }
        $idx = $records[$idxUrl];
        $yahoo = $records[$yahooUrl];
        if (($members['idx_listing_announcement_extract.json']['source_field_values'] ?? null) !== $idx['facts']) {
            throw new \DomainException('SOURCE_EXTRACTION_MISMATCH');
        }
        if (($members['yahoo_chart_extract.json']['source_field_values'] ?? null) !== $yahoo['facts']
            || ($members['yahoo_chart_extract.json']['raw_sha256'] ?? null) !== hash('sha256', $this->bytes($directory.'/yahoo_chart_response.json'))
            || ($members['yahoo_chart_extract.json']['raw_bytes'] ?? null) !== strlen($this->bytes($directory.'/yahoo_chart_response.json'))) {
            throw new \DomainException('SOURCE_EXTRACTION_MISMATCH');
        }
        if (($members['predecessor_binding.json']['predecessor_manifest_sha256'] ?? null) !== self::MANIFEST_HASH
            || ($members['predecessor_binding.json']['retained_issuer_id'] ?? null) !== $assignments['retained_issuer_id']
            || ($members['predecessor_binding.json']['retained_instrument_id'] ?? null) !== $assignments['retained_instrument_id']) {
            throw new \DomainException('SOURCE_PREDECESSOR_BINDING_INVALID');
        }

        $sources = [];
        foreach ([$idxUrl => $idx, $yahooUrl => $yahoo] as $url => $record) {
            $sources[$url] = [
                'facts' => $record['facts'], 'admitted_fields' => $record['admitted_fields'],
                'evidence_class' => $record['evidence_class'], 'source_revision' => $record['source_revision'],
                'source_known_at' => $this->utc($record['source_known_at']),
                'captured_at' => $this->utc($record['captured_at']),
                'review_reference' => 'E-MD-B10-A002-007',
                'capture_metadata' => $url === $idxUrl ? $members['idx_listing_announcement_extract.json'] : $members['yahoo_chart_extract.json'],
                'admission_limits' => $record['admission_limits'],
            ];
        }

        $listingId = $assignments['listing_id'];
        $idxKnown = $sources[$idxUrl]['source_known_at'];
        $idxCaptured = $sources[$idxUrl]['captured_at'];
        $yahooKnown = $sources[$yahooUrl]['source_known_at'];
        $yahooCaptured = $sources[$yahooUrl]['captured_at'];
        $idxValidFrom = $idx['facts']['valid_from'];
        $idxValidTo = $idx['facts']['valid_to'];
        $yahooValidFrom = $yahoo['facts']['valid_from'];
        $yahooValidTo = $yahoo['facts']['valid_to'];
        $revision = static function (string $id, string $type, string $source, string $known, string $recorded, ?string $from, ?string $to, array $data) use ($listingId): array {
            return ['revision_id' => $id, 'identity_id' => $listingId, 'revision_type' => $type, 'state' => 'ADMITTED',
                'valid_from' => $from, 'valid_to' => $to, 'known_at' => $known, 'recorded_at' => $recorded,
                'package_hash' => self::IKPM_LISTING_MANIFEST_HASH, 'source_locator' => $source,
                'supersedes_revision_id' => null, 'data' => $data];
        };
        $revisions = [
            $revision($assignments['listing_revision_id'], 'LISTING', $idxUrl, $idxKnown, $idxCaptured, $idxValidFrom, $idxValidTo, [
                'venue' => $idx['facts']['venue'], 'continuity' => $idx['facts']['continuity'],
                'history_verified_through' => $idx['facts']['history_verified_through'],
                'listing_state' => $idx['facts']['listing_state'], 'change_reason' => $idx['facts']['change_reason'],
            ]),
            $revision($assignments['exchange_symbol_revision_id'], 'SYMBOL', $idxUrl, $idxKnown, $idxCaptured, $idxValidFrom, $idxValidTo, [
                'namespace' => $idx['facts']['namespace'], 'symbol' => $idx['facts']['symbol'],
            ]),
            $revision($assignments['board_revision_id'], 'BOARD', $idxUrl, $idxKnown, $idxCaptured, $idxValidFrom, $idxValidTo, [
                'market_segment' => $idx['facts']['market_segment'], 'board' => $idx['facts']['board'],
            ]),
            $revision($assignments['provider_mapping_revision_id'], 'PROVIDER_MAPPING', $yahooUrl, $yahooKnown, $yahooCaptured, $yahooValidFrom, $yahooValidTo, [
                'namespace' => $yahoo['facts']['namespace'], 'symbol' => $yahoo['facts']['symbol'],
            ]),
        ];
        $holds = [];
        foreach ($members['unresolved.json']['rows'] as $row) {
            $holds[] = ['hold_hash' => hash('sha256', self::IKPM_LISTING_MANIFEST_HASH.'|'.$row['scope']),
                'package_hash' => self::IKPM_LISTING_MANIFEST_HASH, 'scope' => $row['scope'], 'held_facts' => $row['held_facts']];
        }
        return new FoundationRegistry([
            'registry_version' => 'security-identity-registry/v1',
            'packages' => [[
                'package_hash' => self::IKPM_LISTING_MANIFEST_HASH, 'package_version' => self::IKPM_LISTING_VERSION,
                'recorded_at' => $this->utc($admitted['admitted_at']), 'admission_evidence' => 'E-MD-B10-A002-007',
                'sources' => $sources, 'manifest' => $manifest,
                'assignment_sha256' => self::IKPM_LISTING_ASSIGNMENTS_HASH,
                'predecessor_manifest_sha256' => self::MANIFEST_HASH,
                'bounded_source_records' => array_values($records),
            ]],
            'entities' => [[
                'identity_id' => $listingId, 'entity_type' => 'LISTING',
                'parent_identity_id' => $assignments['retained_instrument_id'],
                'package_hash' => self::IKPM_LISTING_MANIFEST_HASH, 'source_locator' => $idxUrl,
            ]],
            'revisions' => $revisions, 'holds' => $holds,
        ]);
    }

    private function readIkpmCurrentWindow(string $directory, string $assignmentPath, string $manifestBytes, array $manifest): FoundationRegistry
    {
        if (($manifest['manifest_version'] ?? null) !== 'foundation_source_basis_manifest_v1') {
            throw new \DomainException('SOURCE_PACKAGE_VERSION_UNSUPPORTED');
        }
        if (hash('sha256', $manifestBytes) !== self::IKPM_CURRENT_MANIFEST_HASH) {
            throw new \DomainException('SOURCE_MANIFEST_FINGERPRINT_MISMATCH');
        }
        if (($manifest['admission']['predecessor_manifest_sha256'] ?? null) !== self::IKPM_LISTING_MANIFEST_HASH
            || ($manifest['admission']['no_predecessor_rewrite'] ?? null) !== true) {
            throw new \DomainException('SOURCE_PREDECESSOR_BINDING_INVALID');
        }

        $members = [];
        foreach ($manifest['files'] ?? [] as $member) {
            $path = $member['path'] ?? null;
            if (!is_string($path) || basename($path) !== $path || isset($members[$path])) {
                throw new \DomainException('SOURCE_MEMBER_PATH_INVALID');
            }
            $bytes = $this->bytes($directory.'/'.$path);
            if (strlen($bytes) !== ($member['bytes'] ?? null) || hash('sha256', $bytes) !== ($member['sha256'] ?? null)) {
                throw new \DomainException('SOURCE_MEMBER_FINGERPRINT_MISMATCH:'.$path);
            }
            $members[$path] = $this->json($bytes);
        }
        foreach (['acquisition_manifest.json', 'admitted_facts.json', 'idx_current_profile_extract.json', 'idx_current_profile_response.json', 'predecessor_binding.json', 'unresolved.json'] as $required) {
            if (!isset($members[$required])) { throw new \DomainException('SOURCE_MEMBER_MISSING:'.$required); }
        }

        $assignments = $this->json($this->verifiedBytes($assignmentPath, self::IKPM_CURRENT_ASSIGNMENTS_HASH));
        if (($assignments['assignment_version'] ?? null) !== 'security-identity-ikpm-current-window-assignments/v1'
            || ($assignments['source_manifest_sha256'] ?? null) !== self::IKPM_CURRENT_MANIFEST_HASH
            || ($assignments['predecessor_manifest_sha256'] ?? null) !== self::IKPM_LISTING_MANIFEST_HASH
            || ($assignments['basis'] ?? null) !== 'E-MD-B10-A002-008'
            || ($assignments['retained_issuer_id'] ?? null) !== '35ff26e0-a043-41c8-8c6e-f7f2d4227214'
            || ($assignments['retained_instrument_id'] ?? null) !== '93453e6d-87b4-4131-8379-a91fb1766fac'
            || ($assignments['retained_listing_id'] ?? null) !== '6eb68d44-81bf-4469-be4f-27ce32ef03d5') {
            throw new \DomainException('RETAINED_ASSIGNMENT_BINDING_INVALID');
        }

        $admitted = $members['admitted_facts.json'];
        if (($admitted['admission_status'] ?? null) !== 'ADMITTED_BOUNDED_IKPM_CURRENT_WINDOW'
            || ($admitted['admission_evidence'] ?? null) !== 'E-MD-B10-A002-008') {
            throw new \DomainException('SOURCE_ADMISSION_REQUIRED');
        }
        $records = $admitted['records'] ?? [];
        if (count($records) !== 1) { throw new \DomainException('SOURCE_RECORD_BINDING_INVALID'); }
        $idxUrl = 'https://idx.id/primary/ListedCompany/GetCompanyProfilesDetail?KodeEmiten=IKPM&language=id-id';
        $idx = $records[0];
        if (($idx['source_record'] ?? null) !== $idxUrl) { throw new \DomainException('SOURCE_RECORD_BINDING_INVALID'); }

        $extract = $members['idx_current_profile_extract.json'];
        $raw = $members['idx_current_profile_response.json'];
        $rawBytes = $this->bytes($directory.'/idx_current_profile_response.json');
        $profile = $raw['Profiles'][0] ?? null;
        if (($extract['source_url'] ?? null) !== $idxUrl
            || ($extract['source_field_values'] ?? null) !== $idx['facts']
            || ($extract['raw_sha256'] ?? null) !== hash('sha256', $rawBytes)
            || ($extract['raw_bytes'] ?? null) !== strlen($rawBytes)
            || ($raw['ResultCount'] ?? null) !== 1
            || !is_array($profile)
            || ($profile['KodeEmiten'] ?? null) !== 'IKPM'
            || ($profile['NamaEmiten'] ?? null) !== 'PT Ikapharmindo Putramas Tbk.'
            || ($profile['PapanPencatatan'] ?? null) !== 'Pengembangan'
            || ($profile['TanggalPencatatan'] ?? null) !== '2023-11-08T00:00:00') {
            throw new \DomainException('SOURCE_EXTRACTION_MISMATCH');
        }
        if (($members['predecessor_binding.json']['predecessor_manifest_sha256'] ?? null) !== self::IKPM_LISTING_MANIFEST_HASH
            || ($members['predecessor_binding.json']['retained_issuer_id'] ?? null) !== $assignments['retained_issuer_id']
            || ($members['predecessor_binding.json']['retained_instrument_id'] ?? null) !== $assignments['retained_instrument_id']
            || ($members['predecessor_binding.json']['retained_listing_id'] ?? null) !== $assignments['retained_listing_id']) {
            throw new \DomainException('SOURCE_PREDECESSOR_BINDING_INVALID');
        }

        $source = [
            'facts' => $idx['facts'], 'admitted_fields' => $idx['admitted_fields'],
            'evidence_class' => $idx['evidence_class'], 'source_revision' => $idx['source_revision'],
            'source_known_at' => $this->utc($idx['source_known_at']),
            'captured_at' => $this->utc($idx['captured_at']),
            'review_reference' => 'E-MD-B10-A002-008',
            'capture_metadata' => $extract,
            'admission_limits' => $idx['admission_limits'],
        ];
        $listingId = $assignments['retained_listing_id'];
        $known = $source['source_known_at'];
        $captured = $source['captured_at'];
        $from = $idx['facts']['valid_from'];
        $to = $idx['facts']['valid_to'];
        $revision = static function (string $id, string $type, string $supersedes, array $data) use ($listingId, $known, $captured, $from, $to, $idxUrl): array {
            return ['revision_id' => $id, 'identity_id' => $listingId, 'revision_type' => $type, 'state' => 'ADMITTED',
                'valid_from' => $from, 'valid_to' => $to, 'known_at' => $known, 'recorded_at' => $captured,
                'package_hash' => self::IKPM_CURRENT_MANIFEST_HASH, 'source_locator' => $idxUrl,
                'supersedes_revision_id' => $supersedes, 'data' => $data];
        };
        $revisions = [
            $revision($assignments['listing_revision_id'], 'LISTING', $assignments['supersedes_listing_revision_id'], [
                'venue' => $idx['facts']['venue'], 'continuity' => $idx['facts']['continuity'],
                'history_verified_through' => $idx['facts']['history_verified_through'],
                'listing_state' => $idx['facts']['listing_state'], 'change_reason' => $idx['facts']['change_reason'],
            ]),
            $revision($assignments['exchange_symbol_revision_id'], 'SYMBOL', $assignments['supersedes_exchange_symbol_revision_id'], [
                'namespace' => $idx['facts']['namespace'], 'symbol' => $idx['facts']['symbol'],
            ]),
            $revision($assignments['board_revision_id'], 'BOARD', $assignments['supersedes_board_revision_id'], [
                'market_segment' => $idx['facts']['market_segment'], 'board' => $idx['facts']['board'],
            ]),
        ];
        $holds = [];
        foreach ($members['unresolved.json']['rows'] as $row) {
            $holds[] = ['hold_hash' => hash('sha256', self::IKPM_CURRENT_MANIFEST_HASH.'|'.$row['scope']),
                'package_hash' => self::IKPM_CURRENT_MANIFEST_HASH, 'scope' => $row['scope'], 'held_facts' => $row['held_facts']];
        }
        return new FoundationRegistry([
            'registry_version' => 'security-identity-registry/v1',
            'packages' => [[
                'package_hash' => self::IKPM_CURRENT_MANIFEST_HASH, 'package_version' => self::IKPM_CURRENT_VERSION,
                'recorded_at' => $this->utc($admitted['admitted_at']), 'admission_evidence' => 'E-MD-B10-A002-008',
                'sources' => [$idxUrl => $source], 'manifest' => $manifest,
                'assignment_sha256' => self::IKPM_CURRENT_ASSIGNMENTS_HASH,
                'predecessor_manifest_sha256' => self::IKPM_LISTING_MANIFEST_HASH,
                'bounded_source_records' => $records,
            ]],
            'entities' => [], 'revisions' => $revisions, 'holds' => $holds,
        ]);
    }

    /** Restore only against a caller-supplied governed fingerprint; never derive new IDs. */
    public function readRegistry(string $path, string $approvedSha256): FoundationRegistry
    {
        if (preg_match('/^[a-f0-9]{64}$/D', $approvedSha256) !== 1) { throw new \DomainException('REGISTRY_APPROVED_FINGERPRINT_REQUIRED'); }
        return new FoundationRegistry($this->json($this->verifiedBytes($path, $approvedSha256)));
    }

    private function entity(string $id, string $type, ?string $parent, string $source): array
    {
        return ['identity_id' => $id, 'entity_type' => $type, 'parent_identity_id' => $parent, 'package_hash' => self::MANIFEST_HASH, 'source_locator' => $source];
    }

    private function profile(string $revision, string $identity, string $source, array $data, string $at): array
    {
        return ['revision_id' => $revision, 'identity_id' => $identity, 'revision_type' => 'PROFILE', 'state' => 'ADMITTED', 'valid_from' => null, 'valid_to' => null, 'known_at' => null, 'recorded_at' => $at, 'package_hash' => self::MANIFEST_HASH, 'source_locator' => $source, 'supersedes_revision_id' => null, 'data' => $data];
    }

    private function utc(string $iso): string { return (new \DateTimeImmutable($iso))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'); }
    private function json(string $bytes): array { return json_decode($bytes, true, 512, JSON_THROW_ON_ERROR); }
    private function bytes(string $path): string
    {
        if (!is_file($path) || is_link($path)) { throw new \DomainException('SOURCE_MEMBER_MISSING:'.basename($path)); }
        $bytes = file_get_contents($path);
        if ($bytes === false) { throw new \DomainException('SOURCE_MEMBER_UNREADABLE'); }
        return $bytes;
    }
    private function verifiedBytes(string $path, string $hash): string
    {
        $bytes = $this->bytes($path);
        if (hash('sha256', $bytes) !== $hash) { throw new \DomainException('REGISTRY_FINGERPRINT_MISMATCH'); }
        return $bytes;
    }
}
