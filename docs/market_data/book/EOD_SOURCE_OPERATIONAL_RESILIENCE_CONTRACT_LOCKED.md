# EOD SOURCE OPERATIONAL RESILIENCE CONTRACT (FILLED)

## Current State
BELUM DIIMPLEMENTASI

---

## Observed Problem
- PublicApiEodBarsAdapter kena rate limit
- daily pipeline gagal
- finalize jadi NOT_READABLE

---

## Required Behavior

### Retry
- minimal 3x retry
- hanya untuk rate limit & timeout

### Backoff
- exponential (1s, 3s, 7s)

### Timeout
- connect: wajib
- read: wajib

### Error Handling
Harus classify:
- rate limit
- timeout
- upstream error
- invalid response

### Partial Failure
- boleh lanjut jika sebagian ticker gagal
- tapi coverage harus reflect

### Finalize Rule
- tidak boleh READABLE jika ingest gagal

### Fallback
- gunakan publication lama jika valid

### Rerun
- manual rerun wajib tersedia

---

## Test Requirement
Wajib ada:
- retry test
- rate limit simulation
- finalize on failure test

---

## Final State
LOCKED (BELUM DIIMPLEMENTASI)
