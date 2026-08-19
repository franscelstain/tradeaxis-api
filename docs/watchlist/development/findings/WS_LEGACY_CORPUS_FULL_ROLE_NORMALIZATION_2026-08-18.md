# WS Legacy Corpus Full Role Normalization Finding

> **Record ID:** `F-WS-20260818-08`
> **Status:** RESOLVED BY NORMALIZATION / CURRENT GOVERNANCE FINDING

## Finding

The current folder architecture was cleaner than the original corpus, but the original `docs.zip` contained legacy files whose internal sections mixed research, implementation, finding, evidence, decision, governance, and strategy material. Filename/folder-only migration could therefore leave historical composite content in active development folders or misclassify review decisions as evidence.

## Required remediation

Read every original Watchlist file, preserve it byte-for-byte, audit Markdown section-by-section, create exact role extracts for material composite sources, move completed legacy research/findings out of active development, and machine-validate source/extract integrity.
