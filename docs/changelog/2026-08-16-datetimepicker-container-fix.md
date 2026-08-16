# 2026-08-16 — Datetimepicker container fix (attendance index)

## Bug

The attendance index page throws an uncaught console error when the Bootstrap datetimepicker initializes:

`datetimepicker component should be placed within a non-static positioned container`

It comes from `pc-bootstrap4-datetimepicker`'s `place()` (`node_modules/pc-bootstrap4-datetimepicker/src/js/bootstrap-datetimepicker.js`, ~line 457), which walks up from the input's parent and throws when it finds no ancestor whose `position != static`.

## Root cause

On `resources/views/humanresources/hrdept/attendance/index.blade.php` the `#date` input (~line 17) sits inside a `<div class="col-sm-8 row g-3">` whose position is `static` all the way up to `<html>`.

## Fix

Give the input's immediate wrapper a non-static position using an inline style (the established convention in this codebase):

`<div class="col-sm-8 row g-3" style="position: relative;">`

The sibling picker inputs already wrap every picker input in a `style="position: relative;"` div (no position-relative CSS class is introduced):

- `resources/views/humanresources/hrdept/attendance/edit.blade.php` (~lines 100–136)
- `resources/views/humanresources/hrdept/attendance/attendancedailyreport/index.blade.php` (~line 19)
- `resources/views/humanresources/hrdept/attendance/attendanceremark/_form.blade.php` (~lines 23/35)

No other markup or behavior on the page was changed.

## Affected file

- `resources/views/humanresources/hrdept/attendance/index.blade.php`

## Verification

- `php artisan view:cache` → INFO: Blade templates cached successfully.
- `php artisan view:clear` → cache cleared.
- Full test suite green.

> UNVERIFIED BY DOC WRITER: this role has no tool access, so the actual blade contents and the `view:cache` output could not be re-run here; the draft edit and compilation result reflect the implementer's report and should be confirmed by the coordinator before merging if strict proof is required.