# Finding — `F-MD-B18-A002-004`

- ID: `F-MD-B18-A002-004`
- Raised by: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Owner stage: **`MD-B18`**
- Raised at: 2026-09-10T20:10:00+07:00
- Severity: `P1`
- Status: `PARTIALLY_RESOLVED`
- Class: `DEFECT` + `ENVIRONMENT_BLOCKER`
- Blocks: `MD-S002-R0004` only
- Blocks strategy change: `NO`

## Statement

`MD-S002-R0004` requires:

> deterministic output across supported runtime/locale/concurrency conditions

Three dimensions. **None of the three had a corpus** — the row was recorded as having no executable
proof at all, with `B18ReleaseCandidateCriteriaTest` holding the gap open by asserting the criterion
mapped to an empty guard list.

Executing two of the three found a real defect in the first one tried.

## The defect

`DeterministicHashService::fixedDecimal()` rendered float inputs with `sprintf('%.17g', $value)`.

`%g` is one of PHP's locale-aware conversions. Under any comma-decimal locale — German, French,
Indonesian, most of continental Europe — `sprintf('%.17g', 1.5)` returns `1,5`. The regex that parses
the rendered value immediately below then refuses it, so the platform did not silently produce a
different hash: it **threw `HASH_NUMBER_INVALID` and could not hash the value at all**.

Either outcome is fatal to the claim the row makes. Every artifact hash containing a float — bar
batches, indicator batches, adjustment factors, coverage ratios, canonical documents — was a function
of the host's locale rather than of the data. Two machines that agreed about the facts would disagree
about their identity, and every replay comparison between them would fail for a reason that has
nothing to do with the data. On the declared runtime range this is not theoretical: `(string)` casts
of floats are locale-aware on PHP 7.x, and `composer.json` declares `^7.3|^8.0`.

Nothing in the suite would have said so. Every existing determinism guard ran under the one locale
the developer's machine happened to have.

### The fix

`DeterministicHashService::floatLiteral()` renders the float and then normalizes the locale's decimal
separator, read from `localeconv()`, back to `.`.

`%F` is the documented locale-independent conversion, but it renders in fixed notation, which would
change the token for every value `%g` writes in exponent form and therefore change hashes already
sealed. Normalizing the separator keeps the rendering byte-identical to what a C-locale host has
always produced and makes every other host agree with it. The full market-data suite is green either
side of the change, which is the check that no stored hash moved.

## What is now proven

`B18DeterminismAcrossConditionsTest`:

- **Locale.** An owned decimal field, a batch hash and a canonical document hash each render
  identically under `C` and under a comma-decimal locale. The class restores the process locale in
  `tearDown` even when an assertion fails, and skips rather than passes if the host has no
  comma-decimal locale installed — a skip that says "not executed here" is worth more than a green
  that says nothing.
- **Concurrency**, in the form a single process can execute it: order independence. Concurrent
  producers do not agree on physical row order, so a batch hash that depended on the order rows
  arrived in would give two runs over identical facts two identities. Reversal and rotation both
  leave the hash unchanged, and a one-tick change moves it — otherwise order independence could have
  been bought by ignoring the data.

Probes, each reverted by byte copy with the file verified identical by `md5sum`: restoring the
locale-aware `%g` turns all three locale guards red; making `serializeRows()`'s comparator return `0`
turns the order-independence guard red.

## What remains, and why `MD-S002-R0004` is not bound

**The runtime dimension.** `composer.json` declares `^7.3|^8.0`, so "supported runtime" is a range
spanning two major versions, and a single process runs one of them. Comparing two runtimes needs a
second interpreter; only PHP 7.4.33 is installed on this host, and installing another is an
environment change rather than something this attempt can execute.

A predicate covering three dimensions is not satisfied by two, so the row stays `NOT_ASSESSED` and is
not recorded as proven.

`B18DeterminismAcrossConditionsTest::test_the_supported_runtime_range_still_spans_more_than_one_runtime`
holds this open in executable form: it fails if the declared support ever narrows to a single
runtime, because at that point "across supported runtimes" is a claim about one runtime, the suite
already executes on it, and the row becomes bindable. `B18ReleaseCandidateCriteriaTest` no longer
asserts an empty corpus — it asserts that the corpus exists, that no guard in it claims the runtime
dimension, and that the guard recording why is still present.

## What closing this needs

Either a second supported interpreter available to CI — the two dimensions above then re-execute
under each and the hashes are compared across them — or an owner decision narrowing the declared
supported runtime, which makes the dimension provable on the runtime already in use.

Neither is a defect to fix inside this attempt. The defect this finding names has been fixed; what
remains is an environment.
