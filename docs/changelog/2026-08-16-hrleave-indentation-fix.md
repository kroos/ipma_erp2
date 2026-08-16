# HRLeave casts indentation fix

Date: 2026-08-16

## File changed
`app/Models/HumanResources/HRLeave.php`

## Bug
The recent edit that added `protected $table = 'hr_leaves';` (around line 38) accidentally dropped the leading TAB on the following line. `protected $casts = [` now sat at column 0 (no indentation), while every other property in the file is indented with a single TAB (the `$table` line above it and the commented properties below it).

## Fix
Restored the single leading tab so the line now reads:

    \tprotected $casts = [

(one literal TAB character before `protected`; `\t` shown for visibility)

No other change was made: the `$table` value, the casts content, and the comments are untouched.

## Verification
- `php -l app/Models/HumanResources/HRLeave.php` — result UNVERIFIED (writer has no tool access; coordinator must run and confirm)
- `php artisan view:cache` — result UNVERIFIED (coordinator must run and confirm)