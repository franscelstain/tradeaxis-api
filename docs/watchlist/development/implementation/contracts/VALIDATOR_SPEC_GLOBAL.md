# 03 — Validator Spec (Global) — LOCKED

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


> **Status:** LOCKED (Normative)
> **Doc Role:** Global validator spec contract

> **Change Rules:**
> - Perubahan hanya lewat review; wajib update test/fixtures yang terdampak.
> - Jika menambah failure class baru, wajib tambahkan contoh minimal 1 fixture yang memicu class tersebut.


## Purpose
Menetapkan validator minimum lintas policy untuk memblok drift, paramset cacat, dan referensi dokumen/artefak yang tidak valid.

## Prerequisites
- [`01_POLICY_FRAMEWORK_OVERVIEW.md`](../../../authority/governance/WEEKLY_SWING_POLICY_FRAMEWORK.md)
- [`02_PARAMSET_CONTRACT_GLOBAL.md`](PARAMSET_CONTRACT_GLOBAL.md)

## Validator global memastikan
- Paramset mengikuti kontrak global ([`02_PARAMSET_CONTRACT_GLOBAL.md`](PARAMSET_CONTRACT_GLOBAL.md)).
- Semua parameter punya provenance lengkap: `{ origin, status, bt_target, rationale, change_triggers }`.
- `hash_contract` jelas: field list, formatting policy, dan versinya dikenali.
- Tidak ada referensi dokumen/file yang tidak eksis di paket dokumen.
- Namespace code tidak drift antar layer.

## Minimum failure classes
Validator global minimal harus bisa membedakan:
- missing required key
- unknown key / drift
- type drift
- enum invalid
- audit/provenance schema invalid
- hash contract invalid
- missing artifact/reference

## Policy-specific validator
Validator policy-spesifik berada di folder policy.
Contoh Weekly Swing:
- Paramset validator: [`../contracts/WS_PARAMSET_VALIDATOR_SPEC.md`](WS_PARAMSET_VALIDATOR_SPEC.md)
- Confirm overlay spec: [`../../../authority/strategy/WS_D1_CONFIRM_ACTIONABILITY.md`](../../../authority/strategy/WS_D1_CONFIRM_ACTIONABILITY.md)

## Prinsip hasil validator
- Hasil validator harus deterministik.
- Error bebas/umum dilarang; gunakan code yang eksplisit.
- Context tambahan boleh, tetapi tidak mengganti code utama.
