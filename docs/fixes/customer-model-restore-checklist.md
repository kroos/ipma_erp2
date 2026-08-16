# Acceptance Checklist

## Restore
- [ ] Original content restored via `git show HEAD:app/Models/Customer.php`
- [ ] `use Illuminate\Database\Eloquent\Factories\HasFactory;` import present
- [ ] `use Illuminate\Database\Eloquent\SoftDeletes;` import present
- [ ] Class trait line `use HasFactory, SoftDeletes;` present
- [ ] HasMany import present
- [ ] `hasmanyleavereplacement()` present and returns `$this->hasMany(HRLeaveReplacement::class, ...)`
- [ ] `hasmanyoutstation()` present and returns `$this->hasMany(HROutstation::class, ...)`
- [ ] `hasmanysales()` present and returns `$this->hasMany(Sales::class, ...)`

## Casts
- [ ] `protected $casts` block added immediately after `protected $table`
- [ ] Block TAB-indented, `'id' => 'integer',` with trailing comma

## Verification
- [ ] `php -l app/Models/Customer.php` reports No syntax errors
- [ ] Grep confirms `use HasFactory, SoftDeletes;`
- [ ] Grep confirms `hasmanyleavereplacement`
- [ ] Grep confirms `hasmanyoutstation`
- [ ] Grep confirms `hasmanysales`
- [ ] Grep confirms `'id' => 'integer'`
- [ ] `php artisan test` passes (AGENTS.md §9.1)
- [ ] `npx mix` compiles cleanly (AGENTS.md §9.2)