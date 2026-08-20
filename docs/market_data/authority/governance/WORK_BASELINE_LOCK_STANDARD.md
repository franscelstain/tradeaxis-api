# Work Baseline Lock Standard

Every formal `MD-Bxx-Ayyy` attempt MUST bind an immutable Baseline ID containing at minimum: strategy freeze ID/fingerprints, governance fingerprints, verification epoch, traceability-matrix hash, repository/source revision, working-tree state, schema/config/dependency/toolchain identity, and relevant external dependency contract identity.

No attempt may close without a baseline lock.
