# F-WS-20260819-02 — Multi-role document authority gap

> **Document Type:** FINDING  
> **Authoritative Role:** FINDING  
> **Status:** RESOLVED  
> **Scope:** watchlist documentation architecture  
> **Created:** 2026-08-19

## Observation

Current governance separated folders and lifecycle roles, but did not yet state a universal hard rule that every physical semantic document must have exactly one authoritative role. Legacy normalization still allowed composite bundle exceptions, leaving retained files that combined decision/evidence, evidence/finding/research, or other semantic authorities.

## Impact

Without a hard one-role rule, readers can be unsure which part of a file is authoritative, mutability can become ambiguous, and a decision/evidence/history bundle can bypass the lifecycle rules that were intentionally separated by architecture.

## Evidence

See `E-WS-20260819-02` for the normalization and executable-role-integrity validation.

## Resolution

Adopt `ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md`, fully decompose all retained multi-role legacy sources, remove their duplicate composite copies, register one role per current physical document, and make role purity an executable documentation-integrity check.
