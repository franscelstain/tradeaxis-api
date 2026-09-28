<?php

namespace App\Infrastructure\Persistence\SecurityIdentity;

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;

/** Filesystem admission boundary for exactly the E005-reviewed package. No network fetch. */
final class FrozenSourcePackageReader
{
    public const VERSION = 'foundation-source-basis-20260928-v1';
    public const MANIFEST_HASH = 'ffa6acc11d41a2e219719dc035d381844983407210a3d2c39e5c9e52c7f2f45b';
    public const ASSIGNMENTS_HASH = 'dc86d2a9453b3edaaa32736e4ea5022152916bd97c62c13a84c8e4b29953b4aa';

    public function read(string $directory, string $assignmentPath): FoundationRegistry
    {
        $manifestBytes = $this->bytes($directory.'/manifest.json');
        $manifest = $this->json($manifestBytes);
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
