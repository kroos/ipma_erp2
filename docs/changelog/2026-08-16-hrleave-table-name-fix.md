# HRLeave Table-Name Fix — Changelog

Date: 2026-08-16

## Bug
The `HRLeave` model had no explicit `$table`, so Eloquent derived `h_r_leaves` while the real table is `hr_leaves`. This caused `SQLSTATE[42S02]` 1146 on the attendance date search, and on any HRLeave query.

## Fix
Added `protected $table = 'hr_leaves';` to `app/Models/HumanResources/HRLeave.php`.

## Full-folder audit
A script compared every model's resolved table name against the live DB (`SHOW TABLES`, 176 tables). Only `HRLeave` was wrong. `SalesAmend`'s derived `sales_amends` is correct, and all other 72 models map to existing tables.

## Stray scaffold migration
The stray scaffold migration `database/migrations/2026_08_12_175759_create_h_r_leaves_table.php` was deleted. It created an empty `h_r_leaves` table; nothing referenced it and it was not recorded in the migrations table.

## Verification
- tinker `getTable()` prints `hr_leaves`
- the exact failing query shape returns count 0 with no 42S02
- full suite: 47 passed / 159 assertions

## Writer note — UNVERIFIED
This entry was assembled from the provided change scope without tool access (per coordinator instruction). The following claims must be confirmed against the actual repo before merge: the stray migration is gone from `database/migrations/`, the audit findings (176 tables / only HRLeave wrong), and the verification outputs (getTable, query count, 47/159). Any claim that cannot be confirmed should be removed or annotated.