# F-MD-20260820-02 — Legacy composite semantic-role gap

- Status: `CLOSED`
- Finding: full-content audit found 43 pre-refactor Market Data Markdown sources that materially owned more than one historical semantic role (for example implementation + evidence + decision/finding) while physically stored as one legacy document.
- Constraint: current Market Data strategy authority MUST remain byte-identical and is excluded from semantic rewriting/splitting.
- Required outcome: exact source-range split, 100% line coverage, no overlap/gap, stable LS/LX lineage, removal of composite originals after sealing, and current registries/gates updated.
- Resolution: `D-MD-20260820-02` with evidence `E-MD-20260820-02`.
