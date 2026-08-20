# Tickers and Identity Dependency Contract (LOCKED)

## Purpose

Lock the temporal identity model required by Market Data Platform so acquisition, coverage, publication, replay, and downstream reads bind data to the correct security as-of a trade date without survivorship bias.

## Ownership note

Market Data Platform depends on a shared security-identity foundation. This contract defines required dependency semantics only; it does not transfer ownership of the global master into `market_data` or transfer ownership of bars, indicators, eligibility, and publications out of it.

## Required identity layers (LOCKED)

The following concepts must remain distinct:

1. **Issuer** — the legal/economic issuing entity, identified by immutable `issuer_id`.
2. **Instrument** — the security/equity instrument issued by an issuer, identified by immutable `instrument_id`.
3. **Listing** — the instrument's admission to a venue, market segment, and board over an effective interval, identified by immutable `listing_id`.
4. **Display/exchange symbol** — a time-varying code attached to a listing.
5. **Provider symbol mapping** — a provider-specific, time-varying transport identifier mapped to `listing_id` or `instrument_id` through an explicit mapping record.

Legacy `ticker_id` may remain as a compatibility identity only when its exact equivalence to `instrument_id` or `listing_id` is documented and invariant. New contracts must use stable identity names and must not assume `ticker_code` is the security.

## Temporal fields (LOCKED)

Issuer/instrument/listing/symbol records that can change historical membership must provide:

- stable immutable identity
- `valid_from` or equivalent effective start
- nullable `valid_to` or equivalent exclusive/inclusive end with one documented convention
- status/reason for listing, delisting, relisting, symbol change, or board movement
- source/provenance and revision identity
- `recorded_at`/`known_at` when point-in-time as-known replay is required

Effective-time answers what was true on trade date T. Recorded/known-time answers what the system was allowed to know during an as-known replay. Current fields may be cached projections only and must not replace temporal records.

## Historical universe rule (LOCKED)

Universe membership for trade date T must be resolved entirely as-of T:

- include a listing whose effective interval covers T and whose market/board scope matches the governed product
- exclude a listing before its listed/admission date
- exclude a listing after its effective delisting/termination date
- retain an instrument that is inactive now when it was valid on T
- treat suspension and daily trading status as separate point-in-time facts, not as deletion of historical identity
- apply symbol/board changes using the mapping effective on T

Current `is_active`, current ticker lists, current provider symbols, and present-day board state are forbidden as the sole resolver for historical coverage, replay, or backtest.

## `is_active` boundary (LOCKED)

`is_active` may exist only as a current-state cache or operational query optimization. It must be derived from temporal state, must never erase historical membership, and must never filter an as-of-T universe before temporal resolution.

There is no historical fallback that permits `is_active=1` alone. If the temporal dependency is missing or ambiguous, historical universe resolution must fail/hold with explicit evidence rather than silently use the current universe.

## Stable-key rules (LOCKED)

1. Canonical bars, observations, actions, status events, indicators, eligibility, and publication manifests bind to stable `instrument_id`/`listing_id` semantics.
2. Display ticker codes and provider symbols are never durable join keys by themselves.
3. Symbol reuse must create or resolve through non-overlapping temporal mapping records; the same text code must not attach old history to a different instrument.
4. Board or market-segment movement must be effective-dated and must not rewrite the prior listing context.
5. Delisting followed by relisting must have explicit temporal continuity or a new listing identity according to governed master evidence.
6. Identity corrections create revisions/effective records; they do not silently rewrite identity already bound to sealed publications.

## Required point-in-time resolution output

For requested trade date T, the identity dependency must be able to return at minimum:

- `issuer_id`
- `instrument_id`
- `listing_id`
- display/exchange symbol valid on T
- market segment and board valid on T
- listed/delisted or listing-validity state on T
- provider-symbol mapping valid for provider and T
- source/revision and as-known identity used by the run

The run/publication must record the identity/universe snapshot or immutable version/hash needed to reproduce that resolution.

## Failure behavior (LOCKED)

- Missing temporal identity or overlapping/conflicting mapping for T blocks affected acquisition/universe membership.
- An unmapped provider symbol is rejected/quarantined; it must not fabricate a new stable identity.
- Ambiguous symbol reuse must fail closed.
- Failure for one instrument may remain per-instrument during import, but coverage and promote must expose the resulting gap.

## Consumer impact

Downstream consumers use stable identity and the symbol/listing projection as-of the effective trade date. A current ticker code is presentation metadata, not proof of historical identity.

## Capability boundary (LOCKED)

The rules above make the **resolver** survivorship-free. They cannot make the **master** complete, and those are different guarantees.

**What temporal resolution proves.** That a listing recorded in the master is included or excluded for trade date `T` strictly by its effective interval; that current state never filters a historical universe; that symbol reuse resolves to the correct stable identity on each side of its boundary.

**What it cannot prove.**

- **That the master contains every security that existed on `T`.** A security that listed and delisted without ever being recorded is absent from the universe, absent from the coverage denominator, and absent from both sides of a replay comparison. No gate fires, because every gate derives its expectation from this same master.
- **That a recorded delisting date is the real one.** An effective interval closed on the wrong date silently moves a listing in or out of historical universes.
- **That symbol reuse was noticed.** Reuse handled correctly is provably correct; reuse that was never recorded as reuse resolves to one identity across both eras, and looks entirely consistent.

This is the same structure the market calendar carries: the universe is a **root of expectation**, so no downstream check can detect that it is wrong.

### Universe completeness is verified externally (LOCKED)

The universe is a root of expectation, so it falls under the shared external-reconciliation rules owned by global gate 13 in `Market_Data_Implementation_Conformance_Matrix_LOCKED.md`. Those rules are not repeated here.

Domain parameters owned by this contract:

- **Authority:** an authoritative exchange listing and delisting record.
- **Cadence:** reconcile the current listing universe on each operational trading day before finalizing that date's expectation set; run full-range reconciliation before any historical period is claimed survivorship-free, and rerun after authoritative listing/delisting corrections.
- **Scope:** from the intentional dataset start onward. Absence of securities delisted before that boundary is out of scope by design, not a completeness defect.
- **Qualification:** a survivorship-free claim covering an unreconciled period must name that period explicitly.

## Legacy `ticker_id` retirement (LOCKED)

`ticker_id` is permitted above only as a compatibility identity with documented, invariant equivalence. That condition governs its **use**; it does not state when it ends.

- Stable `instrument_id`/`listing_id` are canonical. `ticker_id` is retained solely so existing rows and consumers do not break.
- The alias is retired once no reader outside this package depends on it, which must be demonstrated rather than assumed.
- Retirement is a versioned schema and read-model change; it never silently drops the column.
- Until retirement, **no new table, column, contract, or API field may key on `ticker_id`**. New surfaces bind stable identity. The alias may be preserved, not propagated.
- A `ticker_id` whose equivalence to a stable identity is not documented and invariant is not a compatibility alias; it is an unresolved identity and must fail closed.

## Acceptance criterion (LOCKED)

An inactive-now-but-active-on-T listing must appear in the historical universe for T, and an active-now-but-not-yet-listed-on-T listing must not. Any resolver that cannot satisfy both cases is survivorship-biased and violates this contract.

Satisfying this criterion establishes a **survivorship-free resolver**. A survivorship-free **universe** additionally requires the external completeness reconciliation above. Claims must name which of the two they mean.

## Cross-contract alignment

- `Symbol_Lifecycle_and_Mapping_Contract.md`
- `Market_Calendar_Requirements_Contract.md`
- `Coverage_Universe_Definition_LOCKED.md`
- `Replay_Verification_Contract_LOCKED.md`
