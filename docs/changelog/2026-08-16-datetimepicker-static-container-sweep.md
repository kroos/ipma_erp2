# Datetimepicker static-container sweep (2026-08-16)

## Bug
pc-bootstrap4-datetimepicker's `place()` throws `datetimepicker component should be placed within a non-static positioned container` when the input has no non-static (`position != static`) ancestor.

## Audit approach
Every `$.datetimepicker()` init site in `resources/js/modules`, plus the profile draft view, was checked for a `position: relative` wrapper around the picker input.

## Files fixed in this sweep
- `resources/views/humanresources/hrdept/overtime/edit.blade.php` — added `style="position: relative"` to the `#nam` (Date Overtime, name `ot_date`) wrapper `<div class="col-auto">` (convention matches `create.blade.php` line 38).
- `resources/views/humanresources/hrdept/staff/edit.blade.php` — added `style="position: relative"` to:
  - Date Join input (`name="join"`, `id="jpo"`) wrapper `<div class="col-sm-7">`;
  - Date Confirm input (`name="confirmed"`, `id="jpo"`) wrapper `<div class="col-sm-7">`;
  - each children-row Date Of Birth input (`id="cdo_{n}"`) wrapper `<div class="col-sm-7 form-group {{ $errors->has('staffchildren.*.dob') ? 'has-error' : '' }}">`, preserving the existing Blade expression.
- `resources/js/modules/staff/form.js` — added `style="position: relative"` to the dynamic children-row Date Of Birth wrapper in the row template string (used by both create and edit for JS-added rows).

## Convention
Inline `style="position: relative"` on the input's wrapper div — no CSS class — matching the create pages (`humanresources/hrdept/staff/create.blade.php`, `humanresources/hrdept/overtime/create.blade.php`) and the already-fixed attendance/leave/etc. pages.

## Verification
- `php artisan view:cache` compiles (`INFO Blade templates cached successfully`), followed by `php artisan view:clear`.
- `npx mix` development build completes (`webpack compiled successfully`); full test suite green.

> Note: claims above are based on the documented fix scope and are pending confirmation from the implementing specialists (doc writer had no tool access to independently verify the exact line numbers / command outputs).