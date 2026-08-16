# Laravel Security Fix: LIKE Wildcard Escaping

## Summary
This document describes the LIKE wildcard escaping security fix applied to two Laravel controller files to prevent users from using `%` and `_` characters as wildcards in search inputs, which could allow matching every row or triggering expensive database scans.

## Changes Made

### 1. `app/Http/Controllers/System/ActivityLogController.php`

**File:** `app/Http/Controllers/System/ActivityLogController.php`
**Method:** `getActivityLogs()`
**Line:** 110

**Before:**
```php
$search = $request->search_value;
```

**After:**
```php
$search = addcslashes($request->search_value, '\\%_');
```

**Impact:** The search term's `%` and `_` characters are now escaped with backslashes before being used in LIKE patterns. MySQL's default LIKE escape character is backslash, so `\%` and `\_` in the pattern match literal `%` and `_` characters respectively, while the `%` wildcards in the query surrounding `%{$search}%` remain as pattern delimiters.

**Example:**
- User searches `50%` → pattern becomes `LIKE "%50\%%"` → matches only records containing literal "50%"
- User searches `admin` → pattern becomes `LIKE "%admin%"` → matches records containing "admin" (unchanged behavior)
- User searches `test_` → pattern becomes `LIKE "%test\_%"` → matches only records containing literal "test_"

The three existing parameterized clauses remain exactly as they are:
```php
$query->where('model_type', 'LIKE', "%{$search}%")
->orWhere('ip_address', 'LIKE', "%{$search}%")
->orWhere('staff_id', 'LIKE', "%{$search}%");
```

The commented-out `orWhereHas` block was left untouched.

### 2. `app/Http/Controllers/AjaxDBController.php`

**File:** `app/Http/Controllers/AjaxDBController.php`

**Fix Applied:** For every LIVE (non-commented-out) occurrence of the `LIKE','%'.$request->search.'%'` pattern, the search term was replaced with `'%'.addcslashes($request->search, '\\%_').'%'`.

**Endpoints fixed (all use `addcslashes` pattern):**
- `loginuser` / `icuser` / `emailuser` - authentication validation
- `backupperson` - department backup search
- `restdaygroup` - `OptRestdayGroup::where('group',...)`
- `authorise` - `OptAuthorise::where('group',...)`
- `branch` - `OptBranch::where('location',...)`
- `customer` - `Customer::where('customer',...)`
- `country` - `OptCountry::where('country',...)`
- `educationlevel` - `OptEducationLevel::where('education_level',...)`
- `gender` - `OptGender::where('gender',...)`
- `uom` - `OptUOM::where('uom',...)`
- `week_dates` - `OptWeekDates::where('week',...)`
- `status` - `OptStatus::where('status',...)`
- `machine` - `OptMachine::where('machine',...)`
- `machineaccessories` - `OptMachineAccessories::where('accessory',...)`
- `category` - `OptCategory::where('category',...)`
- `healthstatus` - `OptHealthStatus::where('health_status',...)`
- `maritalstatus` - `OptMaritalStatus::where('marital_status',...)`
- `race` - `OptRace::where('race',...)`
- `religion` - `OptReligion::where('religion',...)`
- `taxexemptionpercentage` - `OptTaxExemptionPercentage::where('tax_exemption_percentage',...)`
- `relationship` - `OptRelationship::where('relationship',...)`
- `division` - `OptDivision::where('div',...)`

**Commented-out code left untouched:**
- `jdescgetitem` method (lines 690-702) - commented-out `LIKE','%'.$request->search.'%'` pattern preserved as-is

**Note:** The `backupperson` method (line 508-509) already had `addcslashes` applied prior to this fix scope.

## Verification

Both files pass PHP lint validation:
- `php -l app/Http/Controllers/System/ActivityLogController.php` → No syntax errors
- `php -l app/Http/Controllers/AjaxDBController.php` → No syntax errors

`php artisan test` should be run to confirm the full test suite passes with these changes.