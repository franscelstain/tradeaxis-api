# Session Snapshot Retention Defaults (LOCKED)

Default: 30 days.
Purge daily: `captured_at < now() - 30d`.
- purge summary must make it explicit whether cutoff came from manual `before_date` or the default 30-day retention window.
