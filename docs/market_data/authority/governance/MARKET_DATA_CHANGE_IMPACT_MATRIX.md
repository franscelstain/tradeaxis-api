# Market Data Change Impact Matrix

| Change | Strategy revision? | Current implementation proof impact |
|---|---|---|
| Technical refactor with identical behavior | No | affected stage revalidation |
| Schema/config representation change | Usually no | affected stages + migration/backfill/test proof |
| Market-data semantic/formula/readability change | Yes, controlled | invalidate affected rules/stages |
| Historical evidence correction | No | new correction evidence; old evidence preserved |
| Provider adapter implementation change | No if canonical semantics unchanged | source/acquisition and dependent proof revalidation |
