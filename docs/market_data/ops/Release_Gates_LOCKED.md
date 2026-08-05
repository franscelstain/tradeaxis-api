# Release Gates (LOCKED)

A release that changes output-affecting behavior is not ready unless:
- contract tests pass
- golden fixtures still match or are version-bumped intentionally
- replay/data-quality checks are reviewed
- config registry changes are documented
- downstream safety invariants (effective date, seal, fallback) remain intact
## Capability boundary (LOCKED)

**What release gates prove.** That each declared gate was evaluated before a release claim, and that a failing gate blocks the claim rather than being weighed against others.

**What they cannot prove.**

- **That the gate set is sufficient.** Gates encode known failure modes. A release passing every gate is a release nobody could show a reason to stop.
- **That a passing gate was exercised meaningfully.** A gate evaluated against an empty or trivial input passes; passing and being tested are different events.
- **That gate results remain valid.** A gate passed under superseded components attests to that configuration, not to the current one.

Consequently a full gate pass may be cited as evidence that **declared release conditions were met**, never as evidence that **the release is safe**.
