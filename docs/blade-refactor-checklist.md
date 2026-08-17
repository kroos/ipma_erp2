# Blade Refactor Checklist — business logic out of views

Goal (per session plan): no business logic in Blade files → move to services, feed display tables via API endpoints with **client-side DataTables**, and keep JS only in `resources/js/modules/<module>/`.

Severity = `<?php`/`@php` blocks + inline `{{ ...func()... }}` expressions in the blade (higher = worse).

## ✅ Done

| # | Blade | Severity | Date |
|---|-------|----------|------|
| 1 | `humanresources/hrdept/staff/index.blade.php` | (refactored 1st pass) | 2026-08-18 |
| 2 | `humanresources/hrdept/staff/show.blade.php` | (refactored 1st pass) | 2026-08-18 |
| 3 | `humanresources/leave/index.blade.php` (my-leave dashboard) | 39 | 2026-08-18 |
| 4 | `humanresources/hrdept/attendance/attendancereport/storepdf.blade.php` | 25 | 2026-08-18 |
| 5 | `humanresources/hrdept/attendance/attendancereport/store.blade.php` | 30 | 2026-08-18 |

## ⏳ Remaining (ranked worst → bad)

| # | Blade | Severity | Notes |
|---|-------|----------|-------|
| 6 | `humanresources/hrdept/staff/edit.blade.php` | 111 | 99 logic `{{ }}` expr; needs service-decorated model |
| 7 | `humanresources/hrdept/staff/create.blade.php` | 84 | 81 logic `{{ }}` expr |
| 8 | `humanresources/hrdept/appraisal/form/edit.blade.php` | 80 | 70 logic `{{ }}` expr |
| 9 | `humanresources/profile/show.blade.php` | 54 | |
| 10 | `humanresources/hrdept/leave/index.blade.php` | 50 | |
| 11 | `humanresources/hrdept/leave/reject.blade.php` | 36 | |
| 12 | `humanresources/hrdept/appraisal/mark/create.blade.php` | 27 | |
| 13 | `humanresources/hrdept/attendance/edit.blade.php` | 27 | |
| 14 | `humanresources/hrdept/appraisal/mark/show.blade.php` | 26 | |
| 15 | `humanresources/hrdept/attendance/index.blade.php` | 21 | (grid logic partly in AttendanceService already) |
| 16 | `humanresources/hrdept/overtime/overtimereport/index.blade.php` | 17 | |
| 17 | `sales/sales/index.blade.php` | 16 | |
| 18 | `humanresources/hrdept/setting/workinghour/index.blade.php` | 16 | |
| 19 | `humanresources/hrdept/leave/cancel.blade.php` | 16 | |
| 20 | `humanresources/hrdept/leave/edit.blade.php` | 15 | |
| 21 | `humanresources/hrdept/outstation/outstationattendance/index.blade.php` | 14 | |
| 22 | `humanresources/hrdept/leave/_approval_modal.blade.php` | 14 | |
| 23 | `humanresources/hrdept/appraisal/form/show.blade.php` | 13 | |
| 24 | `humanresources/hrdept/overtime/index.blade.php` | 13 | |
| 25 | `humanresources/hrdept/outstation/index.blade.php` | 13 | |
| 26 | `humanresources/hrdept/appraisal/form/printpdf.blade.php` | 12 | |
| 27 | `humanresources/hrdept/overtime/overtimereport/printpdf.blade.php` | 12 | |
| 28 | `humanresources/hrdept/discipline/absent/index.blade.php` | 12 | |
| 29 | `humanresources/hrdept/rleave/index.blade.php` | 12 | |
| 30 | `humanresources/leave/create.blade.php` | 12 | |
| 31 | `humanresources/hrdept/discipline/show.blade.php` | 12 | |
| 32 | `humanresources/hrdept/leave/show.blade.php` | 10 | |
| 33 | `sales/sales/_js.blade.php` | 10 | (bridge partial) |
| 34 | `humanresources/hrdept/overtime/show.blade.php` | 10 | |
| 35 | `humanresources/leave/show.blade.php` | 9 | |
| 36 | `humanresources/hrdept/attendance/attendancedailyreport/index.blade.php` | 8 | |
| 37 | `humanresources/hrdept/setting/holidaycalendar/index.blade.php` | 7 | |
| 38 | `humanresources/hrdept/outstation/create.blade.php` | 7 | |
| 39 | `humanresources/hrdept/setting/mcleave/index.blade.php` | 6 | |
| 40 | `humanresources/hrdept/setting/maternityleave/index.blade.php` | 6 | |
| 41 | `humanresources/hrdept/setting/annualleave/index.blade.php` | 6 | |
| 42 | `humanresources/hrdept/outstation/edit.blade.php` | 6 | |
| 43 | `humanresources/hrdept/appraisal/form/index.blade.php` | 6 | |
| 44 | `humanresources/hrdept/appraisal/apoint/index.blade.php` | 6 | |
| 45 | `humanresources/hrdept/attendance/attendancedailyreport/printpdf.blade.php` | 4 | |
| 46 | `humanresources/hrdept/staff/create.blade.php`/`edit.blade.php` misc | — | |
| 47 | remaining light blades (`leave/create`, `outstationattendance/index`, `setting/*`, `appraisal/list/index`, `rleave/index`, `attendance/index`, `attendance/edit`, `overtime/index`) | 1–5 | |

## How to mark a case done
1. Move all queries/calculations into `app/Services/...` (business logic must not live in views).
2. Interactive tables → API endpoint (AjaxSupportController + `routes/api.php`) + client-side DataTable in `resources/js/modules/<module>/`.
3. Blade keeps only `@foreach`/`{{ }}` echoes; JS only in the module file.
4. Verify: `php -l`, `php artisan view:cache`, `php artisan test`, `npx mix`.
