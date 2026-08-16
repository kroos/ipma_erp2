# Model Fix — DepartmentPivot $casts

**Date:** 2026-08-17
**File:** `app/Models/HumanResources/DepartmentPivot.php`

## Defect

The protected `$casts` block contained the line `'group_id' => 'integer'`, but the live DB column (verified via `SHOW COLUMNS FROM pivot_dept_cate_branches` on `ipmaerp`) is `department_id`. `group_id` does not exist; it was invented by an earlier agent.

## Change

In the `$casts` block, the line

```php
'group_id' => 'integer',
```

was replaced with

```php
'department_id' => 'integer',
```

The rest of the block is unchanged. No other part of the file was touched.

## Verification

- `php -l app/Models/HumanResources/DepartmentPivot.php` → expected **No syntax errors detected** (tester confirmation pending).
- grep: `'department_id' => 'integer'` is present; no `'group_id'` line remains.