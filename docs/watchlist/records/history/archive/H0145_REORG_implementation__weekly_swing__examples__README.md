# Weekly Swing Examples

Folder `examples/` berisi contoh runtime output untuk membantu pembacaan kontrak normatif Weekly Swing. Examples tidak menjadi owner business rule.

## Reading Rule

Jika terdapat perbedaan antara examples dan owner docs, maka owner docs selalu menang.

## Example Inventory

| File | Tujuan | Layer |
|---|---|---|
| `WS_PLAN_RUNTIME_OUTPUT_EXAMPLE_A.json` | contoh PLAN normal | PLAN |
| `WS_PLAN_RUNTIME_OUTPUT_EXAMPLE_NO_TRADE.json` | contoh PLAN no-trade | PLAN |
| `WS_RECOMMENDATION_RUNTIME_OUTPUT_EXAMPLE_A.json` | recommendation normal minimal | RECOMMENDATION |
| `WS_RECOMMENDATION_RUNTIME_OUTPUT_EXAMPLE_B.json` | recommendation normal yang lebih kaya | RECOMMENDATION |
| `WS_RECOMMENDATION_RUNTIME_OUTPUT_EMPTY.json` | recommendation empty minimal | RECOMMENDATION |
| `WS_RECOMMENDATION_RUNTIME_OUTPUT_EMPTY_DETAILED.json` | recommendation empty yang lebih detail | RECOMMENDATION |
| `WS_CONFIRM_RUNTIME_OUTPUT_EXAMPLE_A.json` | confirm untuk final Top Pick dengan valid current data | CONFIRM |
| `WS_CONFIRM_UNAVAILABLE_RETRYABLE_EXAMPLE.json` | current data belum tersedia; core Top Pick tetap valid dan retryable | CONFIRM |
| `WS_PLAN_RECOMMENDATION_PAIR_EXAMPLE_A.json` | pasangan PLAN + RECOMMENDATION | CROSS-LAYER |
| `WS_PLAN_RECOMMENDATION_CONFIRM_TRIPLE_EXAMPLE_A.json` | triple artifact PLAN + RECOMMENDATION + CONFIRM | CROSS-LAYER |
| `WS_PLAN_REC_CONFIRM_COMPOSITE_EXAMPLE.json` | composite consumer view | CONSUMER VIEW |

## Mandatory Reading Notes

- recommendation examples tidak boleh dibaca sebagai hasil yang dibentuk dari confirm;
- confirm examples hanya berlaku untuk final Top Picks dan tidak boleh dibaca sebagai pembentuk recommendation baru;
- absence/unavailable CONFIRM adalah valid state dan tidak menggagalkan core recommendation;
- composite examples hanya untuk tampilan consumer/view;
- field baru tidak boleh diambil dari examples tanpa basis owner docs.
