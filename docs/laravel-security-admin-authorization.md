## Changes

1. **config/auth.php**: Append 'admins' block:
   ```php
   'admins' => ['authorise_id' => 1, 'login_ids' => [117, 72]],
   ```
   with comment explaining legacy markers and migration path to roles/permissions table.

2. **app/Models/Login.php** `isAdmin()` method: Refactor to read from config:
   ```php
   $user->belongstostaff()->where('authorise_id', config('auth.admins.authorise_id'))->exists() || in_array($user->id, config('auth.admins.login_ids', []))
   ```
   Byte-for-byte identical semantics.

3. **app/Providers/AuthServiceProvider.php** `boot()` method:
   - Uncomment/enable Gate facade import if needed.
   - Register Gate 'admin' delegating to model's `isAdmin()`:
     ```php
     Gate::define('admin', function ($user) {
         return $user->isAdmin();
     });
     ```
   - Keep existing Auth::provider registration unchanged.

4. **app/Http/Middleware/RedirectIfNotSystemAdmin.php** `handle()` method:
   ```php
   if (!$request->user() || !$request->user()->can('admin')) {
       return abort('403');
   }
   ```
   Uses Gate instead of direct model call. Other authorization (isHighManagement) untouched.

## Verification
- Run `php -l` on all changed files.
- Run `php artisan test` to confirm no regressions.