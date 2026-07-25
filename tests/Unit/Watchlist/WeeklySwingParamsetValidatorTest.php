<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;
use TestCase;

class WeeklySwingParamsetValidatorTest extends TestCase
{
    private WeeklySwingParamsetValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new WeeklySwingParamsetValidator();
    }

    public function testActiveSupportExampleAndCanonicalValidFixturePass(): void
    {
        foreach ([
            'db/PARAMSET_WS_ACTIVE_EXAMPLE.json',
            'fixtures/paramset_valid.json',
        ] as $relative) {
            $result = $this->validator->validate($this->fixture($relative));
            $this->assertTrue($result['valid'], $relative.' '.json_encode($result['errors']));
            $this->assertSame(40, $result['canonical_payload']['eval']['min_trades_oos']['value']);
            $this->assertSame(120, $result['canonical_payload']['eval']['min_trades']['value']);
            $this->assertSame([], $result['canonical_payload']['grouping']['display_caps']['value']);
        }
    }

    /**
     * @dataProvider invalidFixtureProvider
     */
    public function testNegativeFixturesFailForTheirNormativeRule(string $fixture, string $reasonCode): void
    {
        $payload = $this->fixture('fixtures/'.$fixture);
        unset($payload['_fixture_expected']);
        $result = $this->validator->validate($payload);

        $this->assertFalse($result['valid']);
        $this->assertContains($reasonCode, array_column($result['errors'], 'reason_code'));
    }

    public function invalidFixtureProvider(): array
    {
        return [
            ['paramset_bad_enum.json', 'WS_PARAMSET_ORIGIN_INVALID'],
            ['paramset_bad_eval.json', 'WS_PARAMSET_EVAL_GATE_INVALID'],
            ['paramset_bad_hash_contract.json', 'WS_PARAMSET_HASH_CONTRACT_INVALID'],
            ['paramset_missing_audit_field.json', 'WS_PARAMSET_AUDIT_FIELD_MISSING'],
            ['paramset_missing_required_key.json', 'WS_PARAMSET_REQUIRED_KEY_MISSING'],
            ['paramset_type_drift.json', 'WS_PARAMSET_TYPE_INVALID'],
            ['paramset_unknown_key.json', 'WS_PARAMSET_UNKNOWN_KEY'],
        ];
    }

    public function testNumericStringsAndUnknownNestedKeysFailClosed(): void
    {
        $payload = $this->fixture('fixtures/paramset_valid.json');
        $payload['liquidity']['min_dv20_idr']['value'] = '1000000000';
        $payload['risk']['unknown_threshold'] = $payload['risk']['min_atr14_pct'];

        $result = $this->validator->validate($payload);

        $this->assertFalse($result['valid']);
        $this->assertContains('WS_PARAMSET_TYPE_INVALID', array_column($result['errors'], 'reason_code'));
        $this->assertContains('WS_PARAMSET_UNKNOWN_KEY', array_column($result['errors'], 'reason_code'));
    }

    public function testCanonicalizedObjectKeyOrderRemainsValid(): void
    {
        $first = $this->validator->validate($this->fixture('fixtures/paramset_valid.json'));
        $second = $this->validator->validate($first['canonical_payload']);

        $this->assertTrue($first['valid']);
        $this->assertTrue($second['valid'], json_encode($second['errors']));
        $this->assertSame($first['canonical_hash'], $second['canonical_hash']);
    }

    private function fixture(string $relative): array
    {
        $path = base_path('docs/watchlist/system/policies/weekly_swing/'.$relative);
        $payload = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($payload);
        return $payload;
    }
}
