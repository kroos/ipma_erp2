## Overtimereport Datetimepicker Crash Fix (2026-08-17)

- Bug: /overtimereport date_start / date_end pickers never appeared.
- Root cause: resources/js/modules/overtimereport/index.js dp.change handlers read the picker instance via $el.data('datetimepicker'), but pc-bootstrap4-datetimepicker stores it under 'DateTimePicker' (capital). The first dp.change (fired on focus via useCurrent) threw TypeError, aborting the plugin's show() before the widget was created.
- Fix: rewrote the handlers to call the plugin method API ($de.datetimepicker('minDate', v) / $ds.datetimepicker('maxDate', v)) - the proven pattern from resources/js/modules/excelreport/form.js. The end picker now blocks dates before the start, and the start picker blocks dates after the end (guard resets to false when empty).
- Verified: headless Edge repro (widget now PRESENT on focus), npx mix compiles, php artisan test passes.

## Leaveapproval Modal TypeError + CSS Fix (2026-08-17)

- Bug 1: clicking the approve button threw 'Uncaught TypeError: can't access property backdrop, this._config is undefined'.
- Root cause 1: the approve button (generated in LeaveApprovalService::tableData) carried data-bs-toggle="modal" data-bs-target="#...", but the target modal is not in the DOM until the module's delegated handler appends it from the DataTable row's modal_html. Bootstrap's data-api click handler fires first, calls Modal.getOrCreateInstance(null), and new Modal(null) crashes in _initializeBackDrop (this._config undefined).
- Fix 1: removed data-bs-toggle/data-bs-target from the approve button - the JS module's delegated handler is the sole opener.
- Bug 2: modal markup appeared unstyled.
- Root cause 2: the module appended the modal to $('body'), but all modal styles in resources/css/app.css are scoped under .page-humanresources-hrdept-leave-{type}leaveapproval-index, so they never matched.
- Fix 2: the 4 leaveapproval JS modules now append the modal inside the page wrapper ($('.page-humanresources-hrdept-leave-' + window.data.type + 'leaveapproval-index')).
- Verified: headless Edge repro (no backdrop TypeError, .table-cell styled display=table-cell with border, #left-detail bold), npx mix compiles, php artisan test passes.