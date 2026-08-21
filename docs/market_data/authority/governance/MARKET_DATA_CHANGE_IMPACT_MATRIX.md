# Market Data Change Impact Matrix

| Change | Strategy revision? | Current implementation proof impact |
|---|---|---|
| Technical refactor with identical behavior | No | affected stage revalidation |
| Schema/config representation change | Usually no | affected stages + migration/backfill/test proof |
| Market-data semantic/formula/readability change | Yes, controlled | invalidate affected rules/stages |
| Historical evidence correction | No | new correction evidence; old evidence preserved |
| Provider adapter implementation change | No if canonical semantics unchanged | source/acquisition and dependent proof revalidation |
| Raw evidence-artifact path/manifest/hash/retention mechanics change | No | revalidate affected executed-proof linkage/integrity; do not rewrite immutable issued evidence |
| Governed evidence admission rule change | No strategy change | impact-analysis required for open/new/carry-forward proof and any closure that depends on the changed proof invariant |
