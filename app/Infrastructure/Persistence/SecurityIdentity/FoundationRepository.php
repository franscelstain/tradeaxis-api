<?php

namespace App\Infrastructure\Persistence\SecurityIdentity;

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Domain\SecurityIdentity\RegistryDocument;
use Illuminate\Database\Connection;

final class FoundationRepository
{
    private Connection $db;
    private const TABLES = ['packages' => ['si_packages', 'package_hash'], 'entities' => ['si_entities', 'identity_id'], 'revisions' => ['si_revisions', 'revision_id'], 'holds' => ['si_holds', 'hold_hash']];

    public function __construct(Connection $db) { $this->db = $db; }

    public function transaction(callable $work)
    {
        $name = 'si_write_'.substr(hash('sha256', $this->db->getDatabaseName()), 0, 32);
        if ((int)$this->db->selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$name])->acquired !== 1) { throw new \RuntimeException('FOUNDATION_WRITE_LOCK_UNAVAILABLE'); }
        try { return $this->db->transaction($work); }
        finally { $this->db->selectOne('SELECT RELEASE_LOCK(?) AS released', [$name]); }
    }

    public function snapshot(): FoundationRegistry
    {
        return $this->db->transactionLevel() === 0
            ? $this->db->transaction(fn (): FoundationRegistry => $this->readSnapshot())
            : $this->readSnapshot();
    }

    private function readSnapshot(): FoundationRegistry
    {
        $d = ['registry_version' => 'security-identity-registry/v1'];
        foreach (self::TABLES as $group => [$table, $key]) {
            $rows = $this->db->table($table)->orderBy($key)->limit(10001)->get(['document_json']);
            if (count($rows) > 10000) { throw new \RuntimeException('BOUNDED_REGISTRY_LIMIT'); }
            $d[$group] = [];
            foreach ($rows as $row) { $d[$group][] = json_decode($row->document_json, true, 512, JSON_THROW_ON_ERROR); }
        }
        return new FoundationRegistry($d);
    }

    public function append(FoundationRegistry $registry): void
    {
        $d = $registry->document();
        foreach ($d['packages'] as $row) { $this->insert('packages', $row); }
        usort($d['entities'], static fn (array $a, array $b): int => array_search($a['entity_type'], ['ISSUER', 'INSTRUMENT', 'LISTING'], true) <=> array_search($b['entity_type'], ['ISSUER', 'INSTRUMENT', 'LISTING'], true));
        foreach ($d['entities'] as $row) { $this->insert('entities', $row); }
        $pending = $d['revisions'];
        while ($pending !== []) {
            $next = [];
            foreach ($pending as $row) {
                if ($row['supersedes_revision_id'] !== null && !$this->db->table('si_revisions')->where('revision_id', $row['supersedes_revision_id'])->exists()) { $next[] = $row; continue; }
                $this->insert('revisions', $row);
            }
            if (count($next) === count($pending)) { throw new \DomainException('REVISION_DEPENDENCY_UNRESOLVED'); }
            $pending = $next;
        }
        foreach ($d['holds'] as $row) { $this->insert('holds', $row); }
    }

    private function insert(string $group, array $row): void
    {
        [$table, $key] = self::TABLES[$group];
        $json = RegistryDocument::json($row);
        $existing = $this->db->table($table)->where($key, $row[$key])->value('document_json');
        if ($existing !== null) {
            if ($existing !== $json) { throw new \DomainException('IMMUTABLE_REGISTRY_CONFLICT:'.$key); }
            return;
        }
        $columns = ['packages' => ['package_hash', 'package_version', 'recorded_at'], 'entities' => ['identity_id', 'entity_type', 'parent_identity_id', 'package_hash'], 'revisions' => ['revision_id', 'identity_id', 'revision_type', 'state', 'valid_from', 'valid_to', 'known_at', 'recorded_at', 'package_hash', 'supersedes_revision_id'], 'holds' => ['hold_hash', 'package_hash', 'scope']];
        $this->db->table($table)->insert(array_merge(array_intersect_key($row, array_flip($columns[$group])), ['document_json' => $json]));
    }
}
