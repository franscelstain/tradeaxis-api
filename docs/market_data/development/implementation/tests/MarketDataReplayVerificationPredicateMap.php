<?php

/**
 * `MD-B18-A002` reviewed predicate map.
 *
 * One entry per denominator row, carrying four reviewed facts:
 *
 *   - `context`   the governing parent rule ID, `SECTION:<heading>` where the governing construct
 *                 is a heading carrying no rule row, or `SELF_CONTAINED`;
 *   - `predicate` the normalized testable statement, composed from parent and child text already
 *                 frozen in the owner document. Strategy bytes are not rewritten -- `rule_text`,
 *                 `source_line` and the fingerprint stay verbatim, as section 3 requires;
 *   - `verdict`   whether the guard `MD-B18-A001` bound to this row establishes that predicate:
 *                 `SUPPORTED`, `PARTIAL` (a proper subset) or `UNSUPPORTED` (a different
 *                 obligation). Assigned after reading each guard body, never its method name;
 *   - `basis`     why, in one line.
 *
 * `F-MD-B19-A001-002` is the finding this map answers. Only `SUPPORTED` rows may stay
 * `SATISFIED`. `PARTIAL` is not a weaker pass: it and `UNSUPPORTED` both return the row to
 * `NOT_ASSESSED` until a guard establishes the predicate as written. Narrowing a predicate to fit
 * an existing guard is the move `MARKET_DATA_DOCUMENT_AUTHORITY.md` section 9 forbids.
 */
final class MarketDataReplayVerificationPredicateMap
{
    public const STAGE = 'MD-B18';

    public const ATTEMPT = 'MD-B18-A002';

    public const BASELINE = 'MD-B18-A002-BL001';

    public const CI = 'CI-MD-B18-A002-001';

    public const FINDING = 'F-MD-B19-A001-002';

    public const EXPECTED_DENOMINATOR = 121;

    /** Reviewed verdict counts, asserted by the gate so a silent re-bucketing is visible. */
    public const EXPECTED_VERDICTS = [
        'SUPPORTED' => 33,
        'PARTIAL' => 29,
        'UNSUPPORTED' => 59,
    ];

    /** @var array<string,array<string,string>> */
    public const PREDICATES = [
        'MD-S002-R0003' => [
            'context' => 'MD-S002-R0002',
            'predicate' => 'A release candidate requires: zero unexplained value, null-reason, lineage, config, factor, hash, seal, or publication mismatches in exact publication fixtures;',
            'verdict' => 'PARTIAL',
            'basis' => 'the guard resolves a publication and reason-codes an unsealed one; zero unexplained mismatches across value/null-reason/lineage/config/factor/hash/seal is broader',
        ],
        'MD-S002-R0004' => [
            'context' => 'MD-S002-R0002',
            'predicate' => 'A release candidate requires: deterministic output across supported runtime/locale/concurrency conditions;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'deterministic output across runtime/locale/concurrency requires varying those conditions; the guard runs one replay once',
        ],
        'MD-S002-R0005' => [
            'context' => 'MD-S002-R0002',
            'predicate' => 'A release candidate requires: all anti-survivorship and as-known isolation fixtures passing;',
            'verdict' => 'PARTIAL',
            'basis' => 'requires ALL anti-survivorship and as-known fixtures passing; the guard executes identity-cutoff invisibility only',
        ],
        'MD-S002-R0006' => [
            'context' => 'MD-S002-R0002',
            'predicate' => 'A release candidate requires: all degraded/negative fixtures producing their expected held/failed/unavailable states without silent repair or denominator shrinkage;',
            'verdict' => 'PARTIAL',
            'basis' => 'the negative guard proves one degraded case stops with an error; held/failed/unavailable states across the degraded corpus are not executed',
        ],
        'MD-S002-R0007' => [
            'context' => 'MD-S002-R0002',
            'predicate' => 'A release candidate requires: long-chain ATR and corporate-action results matching independent oracles;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'long-chain ATR and corporate-action results matching independent oracles requires an executed oracle comparison',
        ],
        'MD-S002-R0008' => [
            'context' => 'MD-S002-R0002',
            'predicate' => 'A release candidate requires: corrected publications preserving their predecessors and switching atomically; and',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'predecessor preservation and atomic switching is a publication-lifecycle obligation not executed by this guard',
        ],
        'MD-S002-R0009' => [
            'context' => 'MD-S002-R0002',
            'predicate' => 'A release candidate requires: `BLOCKED` treated as missing proof, never converted to pass.',
            'verdict' => 'SUPPORTED',
            'basis' => 'BLOCKED-never-a-pass is exactly a citation prohibition the corpus guard scans for',
        ],
        'MD-S002-R0010' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Pass rates or row-count similarity cannot compensate for a semantic mismatch in a required invariant.',
            'verdict' => 'SUPPORTED',
            'basis' => 'pass-rate-cannot-compensate is a citation prohibition in scope of the corpus guard',
        ],
        'MD-S002-R0016' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Consequently a metric set may be cited as evidence that **a study ran over an identified data version**, never as evidence about **market-data quality or strategy merit**.',
            'verdict' => 'SUPPORTED',
            'basis' => 'citation boundary for a metric set',
        ],
        'MD-S003-R0002' => [
            'context' => 'SECTION:Exact publication verification',
            'predicate' => 'Required scenario families / Exact publication verification: resolve an explicit immutable publication, not latest/current;',
            'verdict' => 'SUPPORTED',
            'basis' => 'resolving an explicit immutable publication without current-pointer fallback is the subject of the guard',
        ],
        'MD-S003-R0003' => [
            'context' => 'SECTION:Exact publication verification',
            'predicate' => 'Required scenario families / Exact publication verification: verify frozen observations, temporal revisions, config, factors, formulas, artifacts, hashes, manifest, seal, reasons, and terminal state;',
            'verdict' => 'PARTIAL',
            'basis' => 'the guard verifies publication resolution and seal state; frozen observations, factors, formulas and artifact hashes are not all compared here',
        ],
        'MD-S003-R0004' => [
            'context' => 'SECTION:Exact publication verification',
            'predicate' => 'Required scenario families / Exact publication verification: prove an unchanged rerun is byte-identical and does not create a fake correction.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'byte-identical unchanged rerun producing no fake correction is not executed',
        ],
        'MD-S003-R0005' => [
            'context' => 'SECTION:Degraded acquisition and expectation',
            'predicate' => 'Required scenario families / Degraded acquisition and expectation: provider outage remains missing delivery and cannot shrink the denominator;',
            'verdict' => 'SUPPORTED',
            'basis' => 'the negative guard keeps a zero-row provider outage in the manifest, so the denominator cannot shrink',
        ],
        'MD-S003-R0006' => [
            'context' => 'SECTION:Degraded acquisition and expectation',
            'predicate' => 'Required scenario families / Degraded acquisition and expectation: unknown expectation does not become holiday/dormancy;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'unknown expectation not becoming holiday/dormancy is a calendar-expectation rule not executed here',
        ],
        'MD-S003-R0007' => [
            'context' => 'SECTION:Degraded acquisition and expectation',
            'predicate' => 'Required scenario families / Degraded acquisition and expectation: stale/schema-invalid/wrong-date/zero-price observations quarantine or hold;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'quarantine/hold for stale/schema-invalid/wrong-date/zero-price observations is not executed',
        ],
        'MD-S003-R0008' => [
            'context' => 'SECTION:Degraded acquisition and expectation',
            'predicate' => 'Required scenario families / Degraded acquisition and expectation: no prior-date result masquerades as requested-date fresh data.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'no prior-date result masquerading as requested-date fresh data is not executed',
        ],
        'MD-S003-R0009' => [
            'context' => 'SECTION:Temporal identity and status',
            'predicate' => 'Required scenario families / Temporal identity and status: inactive-now/active-then listing remains in the historical universe;',
            'verdict' => 'SUPPORTED',
            'basis' => 'inactive-now/active-then listing remaining in the historical universe is asserted',
        ],
        'MD-S003-R0010' => [
            'context' => 'SECTION:Temporal identity and status',
            'predicate' => 'Required scenario families / Temporal identity and status: symbol change and symbol reuse resolve through stable listing identity;',
            'verdict' => 'SUPPORTED',
            'basis' => 'symbol change and reuse resolving through stable listing identity is asserted',
        ],
        'MD-S003-R0011' => [
            'context' => 'SECTION:Temporal identity and status',
            'predicate' => 'Required scenario families / Temporal identity and status: calendar/session/status revisions respect effective and knowledge time.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'calendar/session/status revisions respecting effective and knowledge time is not asserted by either identity guard',
        ],
        'MD-S003-R0012' => [
            'context' => 'SECTION:Corporate actions and indicators',
            'predicate' => 'Required scenario families / Corporate actions and indicators: synthetic price-break candidates never activate factors;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'synthetic price-break candidates never activating factors is a corporate-action rule; the guard detects factor-set identity drift',
        ],
        'MD-S003-R0013' => [
            'context' => 'SECTION:Corporate actions and indicators',
            'predicate' => 'Required scenario families / Corporate actions and indicators: verified event/factor revision produces coherent structural OHLC/volume;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'coherent structural OHLC/volume from a verified revision is not asserted by a drift-detection guard',
        ],
        'MD-S003-R0014' => [
            'context' => 'SECTION:Corporate actions and indicators',
            'predicate' => 'Required scenario families / Corporate actions and indicators: provider adjusted-close fallback is impossible;',
            'verdict' => 'SUPPORTED',
            'basis' => 'the negative guard proves provider adjusted close never reaches the canonical row',
        ],
        'MD-S003-R0015' => [
            'context' => 'SECTION:Corporate actions and indicators',
            'predicate' => 'Required scenario families / Corporate actions and indicators: long-chain Wilder ATR matches an independent oracle, including a correction whose impact continues beyond fourteen sessions;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'long-chain Wilder ATR matching an independent oracle is not executed; the guard proves fixture independence',
        ],
        'MD-S003-R0016' => [
            'context' => 'SECTION:Corporate actions and indicators',
            'predicate' => 'Required scenario families / Corporate actions and indicators: actual traded value and close-volume proxy never share meaning or field identity.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'traded value versus close-volume proxy field identity is not asserted',
        ],
        'MD-S003-R0017' => [
            'context' => 'SECTION:Correction and read path',
            'predicate' => 'Required scenario families / Correction and read path: prior immutable publication remains auditable;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'prior immutable publication remaining auditable is a correction/read-path obligation',
        ],
        'MD-S003-R0018' => [
            'context' => 'SECTION:Correction and read path',
            'predicate' => 'Required scenario families / Correction and read path: a distinct corrected candidate becomes active only after complete validation and reseal;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'corrected candidate activation after validation and reseal is not executed',
        ],
        'MD-S003-R0019' => [
            'context' => 'SECTION:Correction and read path',
            'predicate' => 'Required scenario families / Correction and read path: concurrent consumers read exactly one publication;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'concurrent consumers reading exactly one publication is a pointer-lifecycle obligation',
        ],
        'MD-S003-R0020' => [
            'context' => 'SECTION:Correction and read path',
            'predicate' => 'Required scenario families / Correction and read path: explicit fallback retains prior effective date and stale/degraded state.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'explicit fallback retaining prior effective date and stale/degraded state is not executed',
        ],
        'MD-S003-R0021' => [
            'context' => 'SECTION:As-known isolation',
            'predicate' => 'Required scenario families / As-known isolation: later master, event, status, calendar, config, formula, and factor revisions are invisible before their recorded/known times;',
            'verdict' => 'SUPPORTED',
            'basis' => 'later revisions invisible before their recorded time is what the cutoff-invisibility guard asserts',
        ],
        'MD-S003-R0022' => [
            'context' => 'SECTION:As-known isolation',
            'predicate' => 'Required scenario families / As-known isolation: a declared later cutoff can expose them without rewriting earlier replay evidence.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'requires a declared later cutoff to expose facts without rewriting earlier evidence; no guard executes a second cutoff',
        ],
        'MD-S003-R0023' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Record replay mode, fixture/manifest hash, requested/effective dates, knowledge cutoff, all frozen revision/snapshot IDs, expected/actual readiness and reason sets, field-level mismatch paths, artifact/manifest/seal hashes, executable build identity, and `PASS`/`FAIL`/`BLOCKED`.',
            'verdict' => 'PARTIAL',
            'basis' => 'the export writes result and reason-code summary; the full frozen revision/snapshot ID set is not asserted present',
        ],
        'MD-S003-R0024' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Fixtures must be independently reviewed semantic oracles. Copying current implementation output into “expected” files without independent derivation is not acceptable proof.',
            'verdict' => 'SUPPORTED',
            'basis' => 'fixtures must be independent oracles and self-generated expectations are refused - exactly the subject of the guard',
        ],
        'MD-S003-R0025' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'All required scenario families pass on MariaDB production semantics and the supported test mirror. Any missing family remains an open proof gap; historical green results for superseded rules do not close it.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'requires all scenario families passing on MariaDB and the mirror; a single service test does not establish suite-wide family coverage',
        ],
        'MD-S003-R0031' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Consequently a clean historical quality replay may be cited as evidence that **recorded decisions were stable and rule-bound**, never as evidence that **historical data was sound**.',
            'verdict' => 'SUPPORTED',
            'basis' => 'citation boundary for a clean quality replay',
        ],
        'MD-S004-R0002' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Each decision timestamp declares a `knowledge_cutoff`. Inputs contain only observations and identity, calendar, status, event, factor, config, and formula revisions recorded/known by that cutoff and effective for the evaluated context.',
            'verdict' => 'PARTIAL',
            'basis' => 'cutoff-bounded inputs asserted for identity only; calendar/status/event/factor/config bounding not executed',
        ],
        'MD-S004-R0003' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Today\'s universe, symbol, sector, action verification, or current publication may not be backfilled into an earlier decision.',
            'verdict' => 'PARTIAL',
            'basis' => 'no-backfill asserted for identity; universe/sector/action-verification/current-publication backfill not executed',
        ],
        'MD-S004-R0004' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Every row/export binds listing identity, requested/effective trade date, knowledge cutoff, as-known replay ID/publication-like artifact ID, read-model version, full config hash, factor/formula versions, and lineage. Availability timestamp is distinct from market trade date.',
            'verdict' => 'PARTIAL',
            'basis' => 'guard enforces publication/cutoff/fixture-hash/config-hash/serialization/build; listing identity, effective date and read-model binding not enforced',
        ],
        'MD-S004-R0005' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Inactive/delisted securities remain present when they were in the temporal universe. Symbol changes/reuse use listing IDs. Late corrections/actions produce a distinct later-known dataset and do not rewrite the earlier-known dataset.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'inactive/delisted presence and listing-ID symbol resolution are temporal-identity obligations; this guard asserts cutoff invisibility',
        ],
        'MD-S004-R0007' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Signal features use the declared coherent analytical product. Simulated execution must separately choose realistic executable prices/times from allowed facts; it may not trade on same-session information before its availability timestamp. Corporate-action cashflow/total-return treatment is explicit and cannot be inferred from provider adjusted close.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'input-construction obligation (analytical product, execution price choice), not a citation rule; the corpus guard never evaluates feature construction',
        ],
        'MD-S004-R0008' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'At minimum prove inactive-now/active-then membership, symbol transition/reuse, late action verification, late config/calendar/status correction, unavailable same-day data, explicit stale fallback, and original-versus-corrected as-known datasets.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'names seven acceptance fixtures to prove; the guard executes none of them as fixtures',
        ],
        'MD-S004-R0011' => [
            'context' => 'MD-S004-R0010',
            'predicate' => 'Capability boundary (LOCKED) - what a point-in-time input set cannot prove: **That the as-known state was complete at that cutoff.** The set contains what the platform had recorded by then. A fact that existed in the market but was never captured is absent from the as-known view and from the later-known view alike, so no comparison reveals it.',
            'verdict' => 'SUPPORTED',
            'basis' => 'capability-boundary statement about what may be claimed',
        ],
        'MD-S005-R0095' => [
            'context' => 'MD-S005-R0090',
            'predicate' => 'Fixtures must prove: exact publication replay reproduces hashes',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'requires exact publication replay to reproduce hashes; the bound guard resolves a publication without pointer fallback and never reruns to compare hashes',
        ],
        'MD-S005-R0096' => [
            'context' => 'MD-S005-R0090',
            'predicate' => 'Fixtures must prove: as-known replay excludes later revisions',
            'verdict' => 'PARTIAL',
            'basis' => 'as-known replay excluding later revisions is asserted for identity only; calendar, status, event, factor and config revisions are not',
        ],
        'MD-S019-R0009' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'This must hold across reruns and replay.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'must hold across reruns and replay - no rerun is executed by the bound guard',
        ],
        'MD-S019-R0066' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: immutable source-observation manifest',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'immutable source-observation manifest is not among the six inputs the refusal guard enforces, and the conditional it belongs to is never executed',
        ],
        'MD-S019-R0067' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: temporal issuer/instrument/listing/symbol and provider-mapping revisions',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'temporal issuer/instrument/listing/symbol and provider-mapping identity is not among the six enforced inputs',
        ],
        'MD-S019-R0068' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: calendar/session/status revisions',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'calendar/session/status revisions are not among the six enforced inputs',
        ],
        'MD-S019-R0069' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: corporate-action event/factor-set revisions',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'corporate-action event/factor-set revisions are not among the six enforced inputs',
        ],
        'MD-S019-R0070' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: full configuration snapshot/hash',
            'verdict' => 'PARTIAL',
            'basis' => 'full configuration snapshot/hash is enforced by the refusal guard, but the reproducibility consequent this antecedent serves is not executed',
        ],
        'MD-S019-R0071' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: price-product and formula/registry versions',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'price-product and formula/registry versions are not among the six enforced inputs',
        ],
        'MD-S019-R0072' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: serialization rules',
            'verdict' => 'PARTIAL',
            'basis' => 'serialization version is enforced; the reproducibility consequent is not executed',
        ],
        'MD-S019-R0073' => [
            'context' => 'MD-S019-R0065',
            'predicate' => 'If replay uses identical: then replay must reproduce identical outputs and identical hashes.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'the consequent - identical inputs must reproduce identical outputs and identical hashes - requires a second run and a byte comparison that no bound guard performs',
        ],
        'MD-S019-R0074' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Publication replay freezes the exact identities above. As-known replay resolves only revisions known by the declared knowledge cutoff. Current state must not leak into either mode.',
            'verdict' => 'PARTIAL',
            'basis' => 'publication replay freezing identities and as-known resolving only known revisions is asserted for identity recency, not for the full frozen identity set',
        ],
        'MD-S020-R0014' => [
            'context' => 'MD-S020-R0008',
            'predicate' => 'Market-data readiness may be admitted only from market-data evidence establishing immutable publication, lineage, reproducibility, and replay',
            'verdict' => 'SUPPORTED',
            'basis' => 'admitting market-data readiness only from qualifying market-data evidence is a citation rule the corpus admissibility guard scans for',
        ],
        'MD-S036-R0007' => [
            'context' => 'SECTION:Runtime ownership',
            'predicate' => 'Evidence and replay must record and compare request mode, import status, promote status, source mode, pointer switch status, and publication state.',
            'verdict' => 'PARTIAL',
            'basis' => 'request mode and publication context are exported; import/promote/source-mode/pointer-switch comparison is not asserted',
        ],
        'MD-S036-R0012' => [
            'context' => 'MD-S036-R0002',
            'predicate' => 'Allowed request modes: `replay_verify`',
            'verdict' => 'SUPPORTED',
            'basis' => 'replay_verify as an allowed request mode is established by the mode guard and its command-surface refusal counterpart',
        ],
        'MD-S036-R0031' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Evidence export must show whether a run is import-only or promoted without requiring direct DB inspection. Replay must compare expected vs actual request mode, source mode, import status, promote status, publication state, pointer state, and reason code. Unexpected import promotion must be a replay mismatch, not a silent pass.',
            'verdict' => 'PARTIAL',
            'basis' => 'export without DB inspection is shown; expected-versus-actual comparison of all named statuses is not',
        ],
        'MD-S040-R0070' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: coverage gate state',
            'verdict' => 'SUPPORTED',
            'basis' => 'coverage_gate_state is asserted present and correct in the exported replay_result',
        ],
        'MD-S040-R0071' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: coverage reason code',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'coverage reason code is not asserted; the exporter could drop it and the guard would stay green',
        ],
        'MD-S040-R0072' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: coverage ratio',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'coverage ratio is present in the fixture but never asserted in the export',
        ],
        'MD-S040-R0073' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: coverage minimum threshold',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'coverage minimum threshold is never asserted in the export',
        ],
        'MD-S040-R0074' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: expected / available / missing bar counts',
            'verdict' => 'PARTIAL',
            'basis' => 'coverage_missing_sample is asserted; expected, available and missing counts are not',
        ],
        'MD-S040-R0075' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: terminal status',
            'verdict' => 'SUPPORTED',
            'basis' => 'terminal status is asserted in both the summary and the exported expected state',
        ],
        'MD-S040-R0076' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: publishability state',
            'verdict' => 'SUPPORTED',
            'basis' => 'publication_publishability_state is asserted in the exported publication context',
        ],
        'MD-S040-R0077' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: final reason code',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'final reason code is never asserted in the export',
        ],
        'MD-S040-R0078' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: effective trade date/fallback date',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'effective trade date and fallback date are in the fixture but never asserted in the export',
        ],
        'MD-S040-R0079' => [
            'context' => 'MD-S040-R0069',
            'predicate' => 'Evidence export and replay verification must preserve: current publication id/version when available',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'current publication id and version are never asserted in the export',
        ],
        'MD-S040-R0080' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'No evidence or replay flow may treat `manual_file` as readable merely because import succeeded.',
            'verdict' => 'PARTIAL',
            'basis' => 'the guard exports one manual_file run as HELD and NOT_READABLE, which exhibits the case; it does not establish the prohibition, since nothing tries to make a successful import readable',
        ],
        'MD-S041-R0032' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Historical processing uses the calendar revision/evidence governed for the replay mode. As-known replay must not use a future calendar correction that was unknown at its cutoff.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'calendar revision governed per replay mode is not asserted by either as-known guard',
        ],
        'MD-S050-R0001' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Replay mode is mandatory and explicit.',
            'verdict' => 'SUPPORTED',
            'basis' => 'only the two locked modes accepted is the subject of the guard',
        ],
        'MD-S050-R0002' => [
            'context' => 'SECTION:Publication replay',
            'predicate' => 'Two replay modes / Publication replay: Reproduces or verifies one historical immutable publication using exactly the observations, temporal master revisions, calendar/status revisions, event/factor revisions, configuration snapshot, formulas/registries, build/adapter versions, serialization rules, and publication manifest frozen with it.',
            'verdict' => 'PARTIAL',
            'basis' => 'publication replay reproduces one historical publication from frozen inputs; the guard resolves it but does not compare the full frozen input set',
        ],
        'MD-S050-R0005' => [
            'context' => 'SECTION:As-known replay',
            'predicate' => 'Two replay modes / As-known replay: It may differ from a historical publication when the selected cutoff, approved as-known configuration, or declared scenario differs. It creates new replay artifacts and never mutates or impersonates the original publication.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'as-known may differ from a historical publication and creates new artifacts without impersonating the original; not asserted',
        ],
        'MD-S050-R0007' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: replay mode, fixture ID/version, requested/effective date, and knowledge cutoff where applicable;',
            'verdict' => 'PARTIAL',
            'basis' => 'replay mode and knowledge cutoff enforced; fixture ID/version and requested/effective date not enforced',
        ],
        'MD-S050-R0008' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: intentional dataset boundary and temporal universe/listing/symbol/provider mappings;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'dataset boundary and temporal universe/listing/symbol/provider mappings are not among the six enforced inputs',
        ],
        'MD-S050-R0009' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: Regular-Market calendar/session and trading-status revisions;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'calendar/session and trading-status revisions are not among the six enforced inputs',
        ],
        'MD-S050-R0010' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: immutable source observation IDs/hashes and adapter/schema/normalization versions;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'observation IDs/hashes and adapter/schema/normalization versions are not among the six enforced inputs',
        ],
        'MD-S050-R0011' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: canonical `RAW` publication/input set;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'canonical RAW publication/input set is not among the six enforced inputs',
        ],
        'MD-S050-R0012' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: corporate-action event revisions, verification states, factor-set revisions, and contamination decisions;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'event revisions, verification states, factor-set revisions and contamination decisions are not enforced',
        ],
        'MD-S050-R0013' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: full configuration snapshot ID/hash;',
            'verdict' => 'SUPPORTED',
            'basis' => 'config_snapshot_hash absence is refused, which is this predicate exactly',
        ],
        'MD-S050-R0014' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: formula, indicator registry, reason registry, price-product, coverage, eligibility, read-model, hash/serialization, and build versions;',
            'verdict' => 'PARTIAL',
            'basis' => 'serialization version enforced; formula, indicator/reason registry, price-product, coverage, eligibility, read-model versions not enforced',
        ],
        'MD-S050-R0015' => [
            'context' => 'MD-S050-R0006',
            'predicate' => 'Every fixture/manifest binds at minimum: expected publication/pointer/seal state and deterministic output/hash assertions.',
            'verdict' => 'PARTIAL',
            'basis' => 'publication identity enforced for exact mode; pointer/seal state and deterministic output/hash assertions not enforced here',
        ],
        'MD-S050-R0016' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Missing input is `BLOCKED`, not permission to query current/latest state.',
            'verdict' => 'PARTIAL',
            'basis' => 'refusal is proven; that the refused state is recorded as BLOCKED rather than a failure is not asserted',
        ],
        'MD-S050-R0017' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Replay must not use today\'s `is_active`, current symbol, current sector, current suspension/status, latest calendar correction, later corporate-action revision, later factor, current config, or latest provider mapping unless that exact revision was frozen/known in the selected mode.',
            'verdict' => 'PARTIAL',
            'basis' => 'prohibits using the current is_active/symbol/sector/status/calendar/action/factor/config; guard covers identity recency only',
        ],
        'MD-S050-R0019' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: a listing active at historical T but inactive today;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: listing active at T but inactive today - no such fixture is executed by this guard',
        ],
        'MD-S050-R0020' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: a symbol change and provider-symbol mapping transition;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: symbol change and provider-symbol mapping transition',
        ],
        'MD-S050-R0021' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: symbol text reused by another listing;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: symbol text reused by another listing',
        ],
        'MD-S050-R0022' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: a calendar/status fact corrected after T;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: calendar/status fact corrected after T',
        ],
        'MD-S050-R0023' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: a corporate action learned or verified later;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: corporate action learned or verified later',
        ],
        'MD-S050-R0024' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: a configuration/formula change after T;',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: configuration/formula change after T',
        ],
        'MD-S050-R0025' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: an original and corrected immutable publication; and',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: original and corrected immutable publication',
        ],
        'MD-S050-R0026' => [
            'context' => 'MD-S050-R0018',
            'predicate' => 'Required fixtures include: a provider outage that cannot disappear through dormancy/current-universe filtering.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'required fixture: provider outage surviving dormancy/current-universe filtering',
        ],
        'MD-S050-R0027' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Publication replay starts from explicit publication identity, never latest/current. Current-read verification is a separate assertion that the pointer resolves a specific publication.',
            'verdict' => 'SUPPORTED',
            'basis' => 'starting from explicit publication identity and never latest/current is exactly what the guard asserts',
        ],
        'MD-S050-R0028' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'As-known replay performs bitemporal resolution with `effective_at <= target context` and `recorded_at <= knowledge_cutoff`; ties and corrections use versioned deterministic rules. Unresolved ambiguity fails closed.',
            'verdict' => 'PARTIAL',
            'basis' => 'bitemporal resolution asserted; tie-breaking and correction ordering not executed',
        ],
        'MD-S050-R0029' => [
            'context' => 'SECTION:Result and evidence',
            'predicate' => 'Result and evidence: `PASS`: all expected values, null reasons, states, lineages, content hashes, manifest, and seal assertions match.',
            'verdict' => 'PARTIAL',
            'basis' => 'a PASS is produced on match; that PASS requires ALL of values, null reasons, states, lineages, hashes, manifest and seal to match is not exhaustively asserted',
        ],
        'MD-S050-R0030' => [
            'context' => 'SECTION:Result and evidence',
            'predicate' => 'Result and evidence: `FAIL`: comparison executed and diverged.',
            'verdict' => 'SUPPORTED',
            'basis' => 'the divergence guard executes a comparison that diverges and reports FAIL',
        ],
        'MD-S050-R0031' => [
            'context' => 'SECTION:Result and evidence',
            'predicate' => 'Result and evidence: `BLOCKED`: required fixture/runtime/input proof was unavailable.',
            'verdict' => 'SUPPORTED',
            'basis' => 'missing expected proof is reported rather than ignored, which is the BLOCKED semantics',
        ],
        'MD-S050-R0032' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Evidence preserves fixture/manifest hashes, actual and expected contexts, mismatch paths, reason distributions, publication/pointer context, executable command/build identity, timestamps, and admission state without requiring a mutable database as the primary explanation.',
            'verdict' => 'PARTIAL',
            'basis' => 'hashes and contexts are preserved; mismatch paths and reason distributions are not all asserted',
        ],
        'MD-S050-R0033' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => '“Command exited successfully” or matching row counts alone is not replay proof.',
            'verdict' => 'SUPPORTED',
            'basis' => 'exit status or row counts alone is not replay proof; the inadmissible verdict is never counted as a pass',
        ],
        'MD-S050-R0035' => [
            'context' => 'MD-S050-R0034',
            'predicate' => 'Every replay result records its mode as a first-class field. A result set in which publication and as-known outcomes are indistinguishable does not satisfy the mandatory-mode rule, regardless of what the invoking command intended.',
            'verdict' => 'PARTIAL',
            'basis' => 'mode is validated on admission; that every persisted result records mode as a first-class field is not asserted by this guard',
        ],
        'MD-S050-R0036' => [
            'context' => 'MD-S050-R0034',
            'predicate' => 'A result carrying no mode is not a publication-replay result by default. It is **unclassified**, and an unclassified result may not be cited as either.',
            'verdict' => 'SUPPORTED',
            'basis' => 'an unmoded result is refused rather than defaulted, which the mode guard establishes',
        ],
        'MD-S050-R0038' => [
            'context' => 'MD-S050-R0037',
            'predicate' => '**Publication-replay results carry no information about anti-survivorship or future-state leakage.** They compare an artifact against inputs frozen with it; a future master, a later action revision, or a later configuration is absent from both sides of that comparison and therefore cannot produce a mismatch.',
            'verdict' => 'SUPPORTED',
            'basis' => 'citation boundary for publication-replay results',
        ],
        'MD-S050-R0039' => [
            'context' => 'MD-S050-R0037',
            'predicate' => 'No volume of publication-replay `PASS` results substitutes for a single as-known fixture. Accumulating them raises confidence in determinism only.',
            'verdict' => 'SUPPORTED',
            'basis' => 'citation boundary: volume of PASS does not substitute',
        ],
        'MD-S050-R0040' => [
            'context' => 'MD-S050-R0037',
            'predicate' => 'The eight anti-survivorship fixtures required below are as-known fixtures. Until as-known replay exists, their absence is not a gap in coverage — it is a gap in capability, and any claim resting on them is unavailable rather than untested.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'classifies the eight fixtures as as-known and governs coverage accounting; the corpus guard does not evaluate fixture classification or coverage',
        ],
        'MD-S050-R0041' => [
            'context' => 'MD-S050-R0037',
            'predicate' => 'A conformance or activation claim that cites replay evidence names which mode produced it. Citing an unmoded or publication-only corpus in support of a point-in-time property is a governance violation.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'a citation rule about naming the mode in a conformance claim; belongs to the corpus admissibility guard, not mode admission',
        ],
        'MD-S050-R0045' => [
            'context' => 'MD-S050-R0044',
            'predicate' => 'What replay cannot prove: **That the values are correct.** Replay compares an output against itself under fixed inputs. A publication computed from a wrong observation, a missing corporate action, or an absent factor reproduces exactly and returns `PASS`. The verdict means "the pipeline agreed with itself", never "the market data is right".',
            'verdict' => 'SUPPORTED',
            'basis' => 'capability boundary: replay cannot prove value correctness',
        ],
        'MD-S050-R0046' => [
            'context' => 'MD-S050-R0044',
            'predicate' => 'What replay cannot prove: **That the source observation was faithful.** Provider error inside an immutable observation is frozen by the same mechanism that guarantees reproducibility.',
            'verdict' => 'SUPPORTED',
            'basis' => 'that replay cannot prove source faithfulness is a capability boundary the observation guard framing establishes',
        ],
        'MD-S050-R0050' => [
            'context' => 'SECTION:Admissibility of a PASS (LOCKED)',
            'predicate' => 'A replay `PASS` may be cited as evidence of **reproducibility and determinism**. It may never be cited as evidence of **data correctness, event completeness, or factor validity**.',
            'verdict' => 'SUPPORTED',
            'basis' => 'explicit admissibility rule for a replay PASS',
        ],
        'MD-S050-R0051' => [
            'context' => 'SECTION:Admissibility of a PASS (LOCKED)',
            'predicate' => 'A replay `PASS` may not close a data-quality finding, release a quarantine, dismiss a corporate-action candidate, or satisfy a continuity check.',
            'verdict' => 'SUPPORTED',
            'basis' => 'explicit prohibition on what a PASS may close',
        ],
        'MD-S050-R0052' => [
            'context' => 'SECTION:Admissibility of a PASS (LOCKED)',
            'predicate' => 'Where an audit claim requires correctness, the admissible evidence is independent — verified event terms, source reconciliation, or exchange-published facts — not a replay verdict.',
            'verdict' => 'SUPPORTED',
            'basis' => 'names the admissible alternative evidence; the corpus guard forbids the substitution',
        ],
        'MD-S050-R0053' => [
            'context' => 'SECTION:Admissibility of a PASS (LOCKED)',
            'predicate' => 'Capability boundary (LOCKED) / Admissibility of a PASS (LOCKED): `BLOCKED` is not a weaker `PASS`. It states that the comparison did not execute.',
            'verdict' => 'SUPPORTED',
            'basis' => 'BLOCKED is not a weaker PASS - a citation rule',
        ],
        'MD-S050-R0056' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Production relock requires executed publication and as-known fixtures, including all anti-survivorship cases above, on the actual production path.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'production relock on the actual production path with all anti-survivorship cases is not executed',
        ],
        'MD-S055-R0025' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Replay uses the mapping effective on trade date T and, for as-known mode, only the revision known by replay cutoff. It must not use a future rename, relisting, provider correction, or current symbol to resolve historical data.',
            'verdict' => 'SUPPORTED',
            'basis' => 'the mapping effective on T with as-known limited to revisions known by the cutoff is exactly what the identity-cutoff guard asserts',
        ],
        'MD-S058-R0069' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'For trade date T, a historical suspension/unsuspension sequence must resolve using only temporal records valid and known under the requested replay mode. A current status lookup or missing provider bar must never substitute for that proof.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'a historical suspension/unsuspension sequence resolving from cutoff-known facts is a trading-status obligation not executed here',
        ],
        'MD-S065-R0003' => [
            'context' => 'MD-S065-R0001',
            'predicate' => 'Any output-affecting config change must be treated as a contract change. reruns must use the registry version effective for the requested trade date or explicitly documented override',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'treating an output-affecting config change as a contract change and constraining reruns is not asserted by a persistence guard',
        ],
        'MD-S082-R0015' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => '`CONFIG_UNBOUND` publications may not be cited in any conformance, replay, or activation claim without naming the state explicitly. Publication replay over them is `BLOCKED`, not `PASS`, since a required bound input is absent.',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'a citation prohibition on CONFIG_UNBOUND publications; it belongs to the corpus admissibility guard, not to bound-input persistence',
        ],
        'MD-S082-R0216' => [
            'context' => 'MD-S082-R0215',
            'predicate' => 'Operational production selects the approved configuration effective for the run context and records when it became known. Two replay modes are distinct: publication replay uses the exact snapshot frozen with the publication',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'operational production selecting the approved configuration effective for the run context is a config-resolution obligation the publication-resolution guard does not execute',
        ],
        'MD-S082-R0217' => [
            'context' => 'MD-S082-R0215',
            'predicate' => 'Operational production selects the approved configuration effective for the run context and records when it became known. Two replay modes are distinct: as-known replay resolves only revisions known by the declared knowledge cutoff, then freezes a new replay snapshot.',
            'verdict' => 'PARTIAL',
            'basis' => 'the negative guard proves an as-known config resolution refuses rather than inventing one; that production selects the approved effective configuration is not asserted',
        ],
        'MD-S082-R0218' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Current registry state must never leak into historical replay. Alternate-scenario runs are explicitly labeled and cannot impersonate the historical publication.',
            'verdict' => 'PARTIAL',
            'basis' => 'current registry state not leaking into historical replay is asserted for identity; registry state generally is not',
        ],
        'MD-S082-R0224' => [
            'context' => 'MD-S082-R0219',
            'predicate' => 'Before seal, validation proves: current environment drift cannot change publication replay',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'a before-seal validation that environment drift cannot change publication replay; no seal-time validation is executed',
        ],
        'MD-S082-R0225' => [
            'context' => 'MD-S082-R0219',
            'predicate' => 'Before seal, validation proves: as-known replay cannot see later revisions',
            'verdict' => 'UNSUPPORTED',
            'basis' => 'a before-seal validation obligation; the bound guard is a unit test of resolution, not a seal-time validation step',
        ],
        'MD-S085-R0452' => [
            'context' => 'SELF_CONTAINED',
            'predicate' => 'Replay reason codes are proof/comparison outcomes. They do not create readable publications',
            'verdict' => 'PARTIAL',
            'basis' => 'replay reason codes are exported as comparison outcomes; that they cannot create a readable publication is not asserted',
        ],
    ];

    /** @return array<int,string> rule IDs whose recorded proof does not establish their predicate */
    public static function notEstablished(): array
    {
        $out = [];
        foreach (self::PREDICATES as $id => $entry) {
            if ($entry['verdict'] !== 'SUPPORTED') {
                $out[] = $id;
            }
        }

        return $out;
    }
}
