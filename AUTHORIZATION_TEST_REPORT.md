Authorization Guard Test Results
================================

New test: tests/Unit/AuthorizationGuardTest.php
- 4 tests, 10 assertions: ALL PASS

Test breakdown:
1. login model has no hardcoded magic values - PASS
   - Verifies Login.php contains config() calls, no literal [117,72] or hardcoded 1
2. auth config has admin markers - PASS
   - Verifies config/auth.php has 'admins' => 'authorise_id' => 1 and 'login_ids' => [117, 72]
3. auth service provider has admin gate - PASS
   - Verifies AuthServiceProvider.php contains Gate::define('admin'
4. system admin middleware uses can() - PASS
   - Verifies middleware uses can('admin') and not raw isAdmin()

Full suite run: 42 passed, 2 failed
- 2 failures in Tests\Feature\OutstationTest are pre-existing PDO DB connection issues (unrelated)
- All other tests remain green

Authorization centralization is confirmed - magic values have been moved to config().