## ActivityLogController.php
- **File**: `app/Http/Controllers/System/ActivityLogController.php`
- **Method**: `getActivityLogs()`
- **Fix applied**: Line 110 — `$search = addcslashes($request->search_value, '\\%_');`
- **Mechanism**: `addcslashes` escapes MySQL LIKE wildcards (`%` and `_`) in the user's search term by prefixing them with a backslash. The three parameterized `WHERE` clauses (`model_type`, `ip_address`, `staff_id`) remain unchanged and use `%{$search}%` — the escaped search term ensures user input `%` or `_` matches literally rather than acting as wildcards.
- **Verification**: `php -l` passes; `php artisan test` — all 31 tests pass including `LikeWildcardEscapeTest`.

## AjaxDBController.php
- **File**: `app/Http/Controllers/AjaxDBController.php`
- **Fix applied**: All 22 live SELECT2/autocomplete endpoints replace raw `%'.$request->search.'%` with `'%'.addcslashes($request->search, '\\%_').'%'`
- **Endpoints fixed** (all use `addcslashes`):
  - `loginuser` staff search (lines 508-509)
  - `restdaygroup` OptRestdayGroup::where('group'...) (line 542)
  - `authorise` OptAuthorise::where('group'...) (line 557)
  - `branch` OptBranch::where('location'...) (line 571)
  - `customer` Customer::where('customer'...) (line 588)
  - `country` OptCountry::where('country'...) (line 611)
  - `educationlevel` OptEducationLevel::where('education_level'...) (line 625)
  - `gender` OptGender::where('gender'...) (line 639)
  - `uom` OptUOM::where('uom'...) (line 654)
  - `week_dates` OptWeekDates::where('week'...) (line 674)
  - `status` OptStatus::where('status'...) (line 707)
  - `machine` OptMachine::where('machine'...) (line 722)
  - `machineaccessories` OptMachineAccessories::where('accessory'...) (line 743)
  - `category` OptCategory::where('category'...) (line 761)
  - `healthstatus` OptHealthStatus::where('health_status'...) (line 775)
  - `maritalstatus` OptMaritalStatus::where('marital_status'...) (line 789)
  - `race` OptRace::where('race'...) (line 803)
  - `religion` OptReligion::where('religion'...) (line 817)
  - `taxexemptionpercentage` OptTaxExemptionPercentage::where('tax_exemption_percentage'...) (line 831)
  - `relationship` OptRelationship::where('relationship'...) (line 845)
  - `division` OptDivision::where('div'...) (line 859)
- **Left untouched**: Commented-out code (e.g., line 693 `// $au = SalesGetItem::where('get_item','LIKE','%'.$request->search.'%')->get();`) and all other endpoints.
- **Verification**: `php -l` passes; `php artisan test` — all 31 tests pass.

## Security Rationale
Unescaped `%` and `_` in LIKE queries allow users to match every row (e.g., searching `%` matches all records) or trigger expensive full-table scans. `addcslashes($search, '\\%_')` uses MySQL's default LIKE escape character (backslash) to literalize these characters in the search term while preserving `%` as word delimiters in the query pattern.