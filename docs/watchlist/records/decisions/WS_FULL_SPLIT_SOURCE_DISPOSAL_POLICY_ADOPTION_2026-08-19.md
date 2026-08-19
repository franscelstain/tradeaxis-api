# Decision — Remove Physical Originals After Fully Sealed Split

> **Decision ID:** `D-WS-20260819-01`
> **Status:** ISSUED / IMMUTABLE
> **Finding:** `F-WS-20260819-01`

## Decision

Adopt `REMOVED_AFTER_FULL_SPLIT` as the canonical storage policy for legacy composite sources whose decomposition is proven 100% complete. Do not retain duplicate original/composite physical files after sealing.

Current clean strategy/implementation derivatives remain because they are current documents, not preserved legacy-original copies. Unsplit sources and registered bundle exceptions may retain physical source files.
