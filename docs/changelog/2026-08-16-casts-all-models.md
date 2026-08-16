# Changelog: Protected $casts added to all models

Date: 2026-08-16

## Summary

Protected `$casts` blocks were added to all **78 model files** under `app/Models`, based on the **live MySQL database schema** (database: `ipmaerp`, MySQL 8.0), following standard Laravel type mapping.

## Type mapping used

- `integer`
- `boolean`
- `float`
- `date`
- `datetime`
- `decimal:2`

## Merge rules

- Pre-existing **active** cast entries were preserved (existing entries win on conflicts).
- Fully commented-out scaffold cast blocks were **replaced** with real ones.

## Notable preserved (kept) casts

- `HRAttendance` — `exception` => `boolean`
- Sales models — `spec_req` / `urgency` => `boolean`, `date_order` / `delivery_at` / `confirm_date` => `date`
- `SalesJobDescription` — `quantity` => `decimal:2`
- `ActivityLog` — `changes` / `snapshot` / `meta` => `array`, `is_critical` => `boolean`
- `Login` — `email_verified_at` => `datetime`, `password` => `hashed`

## Replaced commented-out scaffold blocks

- `HRLeave`
- `SalesAmend`

## References

- Plan file: `ai-multiagents/plans/casts-followup.json`
- Working data: `ai-multiagents/plans/casts_spec.json`
