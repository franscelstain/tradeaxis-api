# Market Data Documentation Architecture

## Canonical layers

`authority → development → records`

- `authority/strategy`: current Market Data behavior/product/operational strategy.
- `authority/governance`: current process/verification authority.
- `development/implementation`: technical realization and active revalidation.
- `development/research`: current research.
- `development/findings`: current finding lifecycle.
- `records/evidence`: evidence.
- `records/decisions`: issued decisions.
- `records/history`: historical/superseded/provenance.

A document's status never changes its role. A historical PASS does not make a history record current authority.
