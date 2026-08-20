# Implementation Residue and Conformance Standard

Residue states:
- `NOT_ASSESSED`
- `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`
- `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`
- `NON_CONFORMANT_HARMFUL_RESIDUE_OPEN`
- `INCONCLUSIVE_RESIDUE_EVIDENCE`

Every implementation stage performs: implement/revalidate → functional tests → negative/fail-safe tests → residue/conformance check → evidence → closure. Reachable behavior contradicting current authority blocks `DONE`.
