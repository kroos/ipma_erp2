<?php

namespace App\Providers\Auth;

use Illuminate\Support\ServiceProvider;


// using this to override Illuminate\Auth\EloquentUserProvider
// what to override
use Illuminate\Auth\EloquentUserProvider as UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

// load hash
use Illuminate\Support\Facades\Hash;

// class EloquentUserProvider extends ServiceProvider
class EloquentUserProvider extends UserProvider
{
	/**
	 * Register services.
	 */
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap services.
	 */
	public function boot(): void
	{
		//
	}

	// to prevent auto hash password by laravel when using plain or old hash driver password
	public function rehashPasswordIfRequired($user, array $credentials, $validated = true)
	{
		// Disable Laravel’s auto password rehash feature
		return;
	}

	public function validateCredentials(UserContract $user, array $credentials)
	{
		$plain = $credentials['password'];

		// M3: hash round-trip — verify against the bcrypt hash instead of comparing plaintext
		if (! Hash::check($plain, $user->getAuthPassword())) {
			return false;
		}

		// legacy: admins (logins 117 / 72) bypass the active flags
		if ($user->staff_id == 117 || $user->staff_id == 72) {
			return true;
		}

		// staff + login must both be active
		return $user->belongstostaff?->active == 1 && $user->active == 1;
	}
}
