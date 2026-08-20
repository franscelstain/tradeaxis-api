# Invalid Bar Storage Policy (LOCKED)

- invalid provider rows must not be inserted into canonical `eod_bars`
- invalid provider rows may be stored in `eod_invalid_bars` for auditability
- downstream readers must ignore `eod_invalid_bars` as data input
- invalid rows must contribute to eligibility reasoning and run telemetry only

## Capability boundary scope (LOCKED)

**Gate 11: not applicable.** Kontrak ini menetapkan di mana dan bagaimana baris tidak valid disimpan serta larangan memakainya sebagai sumber harga. Ia tidak menghasilkan verdict, state, flag, atau signal yang dapat dikutip sebagai bukti tentang data, sehingga tidak memiliki wilayah buta untuk dinyatakan. Mekanisme yang memang menghasilkan keluaran semacam itu menyatakan batasnya pada owner contract-nya masing-masing.
