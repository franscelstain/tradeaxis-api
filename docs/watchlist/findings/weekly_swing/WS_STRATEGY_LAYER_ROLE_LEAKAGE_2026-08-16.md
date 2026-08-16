# WS Strategy Layer Role Leakage — 2026-08-16

> **Doc Role:** FINDING
> **Severity:** DOCUMENTATION ARCHITECTURE / MATERIAL FOR MAINTAINABILITY
> **Behavior defect:** NO direct trading-behavior change identified by this cleanup

## Finding

Canonical Weekly Swing strategy masih menyimpan beberapa content role yang tidak seharusnya menjadi owner strategy:
- migration/history notes pada PLAN and dynamic-selection docs;
- compatibility-history note pada runtime-flow doc;
- implementation reason codes pada CONFIRM;
- physical table/schema/DDL/SQL/test/artifact details pada backtest strategy;
- storage/serialization details pada metric/OOS acceptance docs;
- stale strategy index yang masih mencantumkan implementation documents sebagai core strategy owner.

## Risk

Jika dibiarkan, implementer dapat menganggap implementation/history detail sebagai canonical business rule, atau sebaliknya mengubah strategy hanya untuk mengikuti perubahan schema/test/runtime. Hal ini melanggar documentation authority model yang baru.

## Required Remediation

Pisahkan content berdasarkan role tanpa mengubah active strategy behavior. Strategy hanya mempertahankan behavioral/acceptance semantics; technical translation dipindahkan ke implementation; pre-cleanup mixed snapshots disimpan di documentation migration history.
