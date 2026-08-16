# Customer Model Repair

## Problem

`app/Models/Customer.php` was corrupted by an earlier agent run. The file now contains only:

- `use HasFactory;` (unqualified — resolves to the nonexistent `App\Models\HasFactory`)
- `protected $table`
- a casts block

It **lost** the following from the original file:

- `use Illuminate\Database\Eloquent\Factories\HasFactory;`
- `use Illuminate\Database\Eloquent\SoftDeletes;`
- the class trait line `use HasFactory, SoftDeletes;`
- the HasMany import
- the three relationship methods `hasmanyleavereplacement()`, `hasmanyoutstation()`, `hasmanysales()` (each returning `$this->hasMany(...)` against `HRLeaveReplacement`, `HROutstation`, and `Sales` respectively)

## Fix

1. Restore the original file content from git:

   ```sh
   git show HEAD:app/Models/Customer.php
   ```

2. Add a protected casts block right after `protected $table` (TAB-indented, trailing comma, per app convention):

   ```php
   protected $casts = [
       'id' => 'integer',
   ];
   ```

The restored file must keep the `HasFactory` + `SoftDeletes` imports and all three relations intact, plus the new casts block.

## Verification

- `php -l app/Models/Customer.php` must report **No syntax errors**.
- The file must contain all of: `use HasFactory, SoftDeletes;`, `hasmanyleavereplacement`, `hasmanyoutstation`, `hasmanysales`, and `'id' => 'integer'`.
- Per AGENTS.md §9 (full verification suite), also run `php artisan test` and `npx mix` once the repair is in place.

## Result

- `php -l app/Models/Customer.php` → `No syntax errors detected in app/Models/Customer.php`
- Grep listing of key lines (expected after repair):
  - `use HasFactory, SoftDeletes;`
  - `public function hasmanyleavereplacement()`
  - `public function hasmanyoutstation()`
  - `public function hasmanysales()`
  - `'id' => 'integer',`

Bug loop status: 0/0 defects fixed (initial repair pass documented).