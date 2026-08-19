# One Document, One Authoritative Role Standard

> **Status:** CANONICAL GOVERNANCE  
> **Scope:** seluruh `docs/watchlist/`  
> **Purpose:** memastikan setiap file mempunyai tepat satu tanggung jawab semantic/authority sehingga strategy, governance, implementation, research, finding, evidence, decision, history, registry, dan navigation tidak kembali bercampur.

## 1. Core Rule

Setiap dokumen semantic **MUST mempunyai tepat satu authoritative role**.

Dokumen boleh:
- mereferensikan record dari role lain;
- merangkum supporting context secukupnya;
- menyimpan ID/link yang membentuk traceability.

Dokumen **MUST NOT**:
- menjadi owner dua atau lebih role semantic sekaligus;
- menyimpan actual result/evidence payload sebagai authority kedua di dalam decision/research/implementation document;
- menyisipkan current strategy/governance rule baru ke implementation, evidence, finding, decision, history, README, atau registry;
- memakai “bundle exception” untuk mempertahankan multi-authority semantic document.

Ringkasnya:

```text
ONE FILE
  -> ONE AUTHORITATIVE ROLE
  -> MANY REFERENCES ARE ALLOWED
  -> MULTIPLE AUTHORITIES ARE NOT
```

## 2. Authoritative Roles

Allowed primary roles untuk current/future documents:

- `STRATEGY`
- `GOVERNANCE`
- `IMPLEMENTATION_CONTRACT`
- `IMPLEMENTATION_GUIDE`
- `IMPLEMENTATION_TEST`
- `IMPLEMENTATION_TOOL`
- `IMPLEMENTATION_DB`
- `STATUS_LEDGER`
- `STAGE_REGISTER`
- `GENERATED_SUMMARY`
- `RESEARCH`
- `FINDING`
- `EVIDENCE`
- `DECISION`
- `HISTORY`
- `LEGACY_SOURCE`
- `NAVIGATION`
- `REGISTRY`
- `TEMPLATE`
- `EXAMPLE`

`README`, `START_HERE`, matrix, registry, attempt record, closure record, atau generated summary bukan multi-role exception. Masing-masing tetap mempunyai satu role khusus seperti `NAVIGATION`, `REGISTRY`, `TEMPLATE`, atau record role yang didefinisikan governance terkait.

## 3. Supporting Context vs Second Authority

### Valid

Decision:

```text
Decision: use approach B.
Supporting Evidence: E-WS-B04-A003-004
Related Finding: F-WS-B04-A003-001
```

Role file tersebut tetap `DECISION`.

### Invalid

Satu decision file berisi sekaligus:
- full PHPUnit/runtime result yang menjadi satu-satunya evidence;
- root-cause finding yang hanya hidup di file itu;
- implementation procedure yang menjadi technical owner;
- final decision.

Itu adalah composite authority dan wajib dipisah.

## 4. Folder Boundary

Physical folder menunjukkan default role family:

```text
authority/strategy/              -> STRATEGY (README = NAVIGATION)
authority/governance/            -> GOVERNANCE/REGISTRY/NAVIGATION

development/implementation/      -> implementation roles/status/tooling
development/research/            -> RESEARCH
development/findings/            -> FINDING

records/evidence/                -> EVIDENCE
records/decisions/               -> DECISION
records/history/                 -> HISTORY/REGISTRY/LEGACY_SOURCE
```

Folder tidak boleh dipakai untuk melegalkan isi yang memiliki authority role berbeda. Placement dan isi harus konsisten.

## 5. Normative Wording Outside `authority/`

Kata seperti `MUST`, `wajib`, atau `tidak boleh` di luar `authority/` **boleh** bila hanya mengatur role file tersebut.

Contoh:
- implementation contract boleh mewajibkan DTO mempertahankan canonical field;
- test contract boleh mewajibkan negative test;
- decision boleh menyatakan keputusan issued;
- evidence boleh menyatakan apa yang diuji/dibuktikan.

Namun dokumen tersebut tidak boleh menciptakan atau override current strategy/governance meaning.

## 6. Legacy Composite Rule

Legacy file yang benar-benar memegang lebih dari satu semantic role harus:

1. dibaca penuh dan diaudit section-by-section;
2. dipecah menjadi role-pure `LX-*` records;
3. mencapai 100% source-line coverage dengan zero overlap;
4. mempunyai source/extract hash dan reconstruction index;
5. original composite dihapus setelah split seal;
6. duplicate mapped composite record juga dihapus;
7. current derivative yang sudah bersih boleh tetap ada dengan satu current role.

**Bundle exception tidak boleh mengesampingkan rule ini.** Bundle exception hanya boleh mempertahankan source yang semantic role-nya tunggal atau container yang role tunggalnya memang `NAVIGATION`, `REGISTRY`, `STATUS_LEDGER`, `LEGACY_SOURCE`, `TEMPLATE`, atau sejenisnya.

## 7. Current/Future Document Creation Rule

Setiap current/future material document harus mempunyai satu role yang tercatat di:

[`DOCUMENT_ROLE_REGISTRY.csv`](DOCUMENT_ROLE_REGISTRY.csv)

Jika file baru dibuat:
1. tentukan role sebelum isi berkembang;
2. register path + role;
3. jika kebutuhan kedua ternyata menjadi authority sendiri, buat file/record baru dan hubungkan dengan ID/link;
4. jangan memperbesar file lama menjadi composite authority.

## 8. Mutability Follows Role

Role tidak menggantikan lifecycle standard. Mutability tetap mengikuti [`DOCUMENT_RECORDING_STANDARD.md`](DOCUMENT_RECORDING_STANDARD.md).

Contoh:
- strategy/governance = `CONTROLLED_REVISION`;
- evidence/issued decision/history = immutable sesuai lifecycle;
- implementation/status/navigation = mutable-traceable sesuai role.

Satu file tidak boleh memakai role kedua untuk menghindari mutability rule role utamanya.

## 9. Integrity Gate

Executable documentation gate wajib memeriksa:

1. `DOCUMENT_ROLE_REGISTRY.csv` mempunyai tepat satu row per physical document yang berada dalam scope;
2. `document_path` unik;
3. role merupakan scalar allowed role, bukan list gabungan;
4. file benar-benar ada;
5. role konsisten dengan physical area;
6. retained legacy source tidak mempunyai lebih dari satu semantic role pada section audit;
7. multi-role legacy source tidak boleh tercatat sebagai bundle exception;
8. fully split composite tidak mempunyai duplicate physical source/composite copy;
9. current strategy/governance ownership tidak bocor ke role lain.

Gate FAIL memblokir package/stage closure yang relevan.

## 10. Hard Rule

**Cross-role reference is allowed. Cross-role authority is not.**

Jika informasi dapat berdiri sendiri sebagai evidence, finding, decision, research rule, implementation contract, strategy rule, atau governance rule, maka informasi tersebut harus hidup pada record role yang sesuai dan dokumen lain hanya menunjuk atau merangkum secukupnya.
