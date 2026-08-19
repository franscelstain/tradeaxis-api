# Finding — Fully Split Legacy Sources Were Still Duplicated

> **Finding ID:** `F-WS-20260819-01`
> **Status:** RESOLVED_BY_DECISION

## Observation

The full semantic normalization correctly decomposed composite legacy documents, but still retained byte-for-byte `LS-*` source copies and, for many sources, a second full composite record. Once decomposition is complete, those copies add storage/noise without serving current authority.

## Material risk

Duplicate composite files make readers unsure whether to use the role-pure records or the old bundle, and undermine the goal of a clean `authority -> development -> records` architecture.

## Required correction

A split source may be physically removed only after 100% line coverage, zero overlap, extract-hash validation, and pre-delete source-SHA validation are sealed. Current clean derivatives remain.
