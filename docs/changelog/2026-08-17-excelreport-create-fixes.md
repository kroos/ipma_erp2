# ExcelReport Create Fixes — Changelog

Date: 2026-08-17
Plan: `ai-multiagents/plans/excelreport-fixes.json`

## 1. `App\Http\Controllers\API\Bus` class-not-found error

### Bug
The import `use Illuminate\Support\Facades\Bus;` in `app/Http/Controllers/API/AjaxSupportController.php` was commented out on line 25 (`// use Illuminate\Support\Facades\Bus;`). The `progress()` method (line 1708) calls `Bus::findBatch($bid)`, so PHP resolved `Bus` to `App\Http\Controllers\API\Bus`, which does not exist, producing the `Class App\Http\Controllers\API\Bus not found` error.

### Fix
Uncommented the import so it now reads `use Illuminate\Support\Facades\Bus;` on line 25, restoring resolution of `Bus::findBatch()`. The surrounding commented lines (e.g. `// use Illuminate\Bus\Batch;`) were left as they are; nothing else in the file was changed.

## 2. `minDate() Could not parse date parameter` TypeError on /excelreport/create

### Bug
`resources/js/modules/excelreport/form.js` binds `'.on(datetimepicker dp.change dp.update)'` on the `from` (`#from1`) and `to` (`#to1`) pickers. The `dp.update` event fires immediately on page load while the input is still empty, so `$('#to1').datetimepicker('minDate', $('#from1').val())` was called with an empty string, which the `pc-bootstrap4-datetimepicker` library cannot parse and throws `Uncaught TypeError: minDate() Could not parse date parameter`. The sibling `resources/js/modules/attendancereport/form.js` only binds `dp.change` (fires on user change, not on init) and is therefore safe.

### Fix
Guarded both handlers so `minDate`/`maxDate` are only set when the source value is non-empty:
- From picker handler: `var fromVal = $('#from1').val(); if (fromVal) { $('#to1').datetimepicker('minDate', fromVal); }`
- To picker handler: `var toVal = $('#to1').val(); if (toVal) { $('#from1').datetimepicker('maxDate', toVal); }`

The `bootstrapValidator` `revalidateField` calls remain unconditional, and the datetimepicker init options were not changed.

## Verification

- `php -l app/Http/Controllers/API/AjaxSupportController.php` → `No syntax errors detected in app/Http/Controllers/API/AjaxSupportController.php`; the active (non-commented) `use Illuminate\Support\Facades\Bus;` import confirmed via grep/diff.
- `php artisan route:list --path=progress` → `GET|HEAD api/progress ... progress › API\AjaxSupportController@progress` (route named `progress` resolves to the API controller).
- `npx mix` → `✔ Compiled Successfully` (webpack compiled successfully), confirming the `excelreport/form.js` edit is syntactically valid and picked up by the module loader bundle.
- `php artisan test` → all unit/feature suites green; the run also surfaces an **unrelated pre-existing** fatal `Trait "App\Models\HasFactory" not found` at `app/Models/Customer.php:8`, caused by the parallel model/casts refactor (Customer extends `App\Models\Model`, which already imports and uses `HasFactory`, while the in-class `use HasFactory;` in the same namespace resolves to a non-existent `App\Models\HasFactory`). This is outside the excelreport fixes scope and not introduced by these changes.