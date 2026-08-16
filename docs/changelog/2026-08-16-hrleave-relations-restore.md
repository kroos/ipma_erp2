# HRLeave relations restore — 2026-08-16

## Bug

`Call to undefined method` fired on `HRLeave::hasmanyleaveapprovalbackup()`. The `HRLeave` model (`app/Models/HumanResources/HRLeave.php`) had lost **all 19 relationship methods** — its relationship section was empty.

~50 call sites across the codebase relied on them:
- `app/Services/HumanResources/LeaveApprovalService.php` (e.g. `hasmanyleaveapprovalbackup()`, `hasmanyleaveamend()`)
- `app/Services/HumanResources/AttendanceService.php`
- `HRLeaveController`, `ModelAjaxCRUDController`
- the `h-r-leave` blades (`resources/views/humanresources/h-r-leave/*`)

## Fix

Restored the 19 relations **verbatim** from the git-history version (commit `fe5f58c`):

- `HasMany` (7): `hasmanyleaveamend`, `hasmanyleaveapprovalbackup`, `hasmanyleaveapprovalsupervisor`, `hasmanyleaveapprovalhod`, `hasmanyleaveapprovaldir`, `hasmanyleaveapprovalhr`, `hasmanyattendance`
- `BelongsToMany` (4): `belongstomanyleaveannual`, `belongstomanyleavemc`, `belongstomanyleavematernity`, `belongstomanyleavereplacement`
- `BelongsTo` (3): `belongstostaff`, `belongstooptleavetype`, `belongstooptleavestatus`
- `HasOne` (5): `hasoneleaveapprovalbackup`, `hasoneleaveapprovalsupervisor`, `hasoneleaveapprovalhod`, `hasoneleaveapprovaldir`, `hasoneleaveapprovalhr`

Also restored the relation-type imports (`HasOne`, `HasMany`, `BelongsTo`, `BelongsToMany`) while keeping the existing `$table = 'hr_leaves'` fix and the `$fillable` / `$casts` blocks.

## Secondary defect caught during verification

In the first restore pass `belongstostaff()` pointed at a non-existent `HumanResources` `Staff` class. Corrected to the real `App\Models\Staff` (class defined at `app/Models/Staff.php`).

## Verification

- `php -l` clean
- 19/19 relationship methods present in `HRLeave`
- Full test suite green: **47 passed / 159 assertions**
- Live DB smoke query across the relations ran without error
