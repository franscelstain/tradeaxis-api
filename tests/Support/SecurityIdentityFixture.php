<?php

namespace Tests\Support;

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;

/** Independently declared synthetic facts: mechanism fixture, never historical master evidence. */
final class SecurityIdentityFixture
{
    public const ISSUER = '11111111-1111-4111-8111-111111111111';
    public const INSTRUMENT = '22222222-2222-4222-8222-222222222222';
    public const LISTING = '33333333-3333-4333-8333-333333333333';

    public static function registry(): FoundationRegistry
    {
        $hash = hash('sha256', 'INDEPENDENT_SYNTHETIC_SECURITY_IDENTITY_MECHANISM_FIXTURE_V1');
        $locator = 'test-only://independent-security-identity-mechanism/v1';
        $facts = [
            'name' => 'Synthetic issuer', 'venue' => 'TEST_VENUE', 'continuity' => 'NEW_LISTING',
            'listing_state' => 'LISTED', 'change_reason' => 'SYNTHETIC_INITIAL_ADMISSION',
            'history_verified_through' => '2030-01-01 00:00:00',
            'namespace' => 'TEST_PROVIDER', 'symbol' => 'OLD', 'market_segment' => 'TEST_SEGMENT', 'board' => 'TEST_BOARD',
            'valid_from' => '2020-01-01 00:00:00', 'valid_to' => null,
        ];
        $source = ['facts' => $facts, 'admitted_fields' => array_keys($facts), 'evidence_class' => 'PRIMARY_MASTER_EVIDENCE', 'source_revision' => 'synthetic-v1', 'source_known_at' => '2020-01-01 00:00:00', 'captured_at' => '2020-01-01 00:00:00', 'review_reference' => 'TEST_ONLY_INDEPENDENT_DECLARATION'];
        $entities = [];
        foreach ([[self::ISSUER, 'ISSUER', null], [self::INSTRUMENT, 'INSTRUMENT', self::ISSUER], [self::LISTING, 'LISTING', self::INSTRUMENT]] as [$id, $type, $parent]) {
            $entities[] = ['identity_id' => $id, 'entity_type' => $type, 'parent_identity_id' => $parent, 'package_hash' => $hash, 'source_locator' => $locator];
        }
        $rows = [
            ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', self::ISSUER, 'PROFILE', ['name' => $facts['name']]],
            ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', self::LISTING, 'LISTING', array_intersect_key($facts, array_flip(['venue', 'continuity', 'history_verified_through', 'listing_state', 'change_reason']))],
            ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', self::LISTING, 'SYMBOL', ['namespace' => 'TEST_PROVIDER', 'symbol' => 'OLD']],
            ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa4', self::LISTING, 'BOARD', ['market_segment' => 'TEST_SEGMENT', 'board' => 'TEST_BOARD']],
            ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa5', self::LISTING, 'PROVIDER_MAPPING', ['namespace' => 'TEST_PROVIDER', 'symbol' => 'OLD']],
        ];
        $revisions = [];
        foreach ($rows as [$id, $entity, $kind, $data]) {
            $revisions[] = ['revision_id' => $id, 'identity_id' => $entity, 'revision_type' => $kind, 'state' => 'ADMITTED', 'valid_from' => $facts['valid_from'], 'valid_to' => null, 'known_at' => $source['source_known_at'], 'recorded_at' => $source['captured_at'], 'package_hash' => $hash, 'source_locator' => $locator, 'supersedes_revision_id' => null, 'data' => $data];
        }
        return new FoundationRegistry(['registry_version' => 'security-identity-registry/v1', 'packages' => [['package_hash' => $hash, 'package_version' => 'test-only-independent-fixture/v1', 'recorded_at' => $source['captured_at'], 'admission_evidence' => 'TEST_ONLY_NOT_PRODUCTION_ADMISSION', 'sources' => [$locator => $source]]], 'entities' => $entities, 'revisions' => $revisions, 'holds' => []]);
    }
}
