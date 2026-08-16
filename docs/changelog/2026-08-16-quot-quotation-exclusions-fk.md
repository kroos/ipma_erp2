# Migration hygiene: quot_quotation_exclusions foreign keys (2026-08-16)

## Context

In `database/migrations/2026_08_08_100825_create_quot_quotation_tables.php`, the `Schema::create('quot_quotation_exclusions', ...)` block declared only `id`, `quot_id`, `exclusion_id`, `remarks`, `created_at`, `updated_at` + the primary key — with **no foreign keys** — while every sibling pivot table in the same migration declares them.

## Change

Added the following declarations inside the existing `quot_quotation_exclusions` block (~lines 153-161), matching the sibling `quot_quotation_remarks` block's order and style:

```php
$table->index(['quot_id'], 'quot_id');
$table->index(['exclusion_id'], 'exclusion_id');

$table->foreign('quot_id', 'quot_quotation_exclusions_ibfk_1')
    ->references('id')->on('quot_quotations')
    ->restrictOnDelete()->restrictOnUpdate();

$table->foreign('exclusion_id', 'quot_quotation_exclusions_ibfk_2')
    ->references('id')->on('quot_exclusions')
    ->restrictOnDelete()->restrictOnUpdate();
```

## Scope guardrails honored

- Only the exclusions block was touched; no other table or block was modified.
- The `down()` drop order was **not** changed.
- No new migration file was created (edits in place only).

## Verification

- `php -l database/migrations/2026_08_08_100825_create_quot_quotation_tables.php` → parses cleanly (no syntax errors).
- `php artisan migrate` was **NOT** run (MariaDB is not running on this machine).
- This is part 2 of 2 of the bug loop (1/2 defects fixed).