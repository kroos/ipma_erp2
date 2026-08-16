<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// auditable model
use App\Traits\Auditable;

use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// load string helper if somehow user not passing an array
use Illuminate\Support\Str;

class Login extends Authenticatable // implements MustVerifyEmail
{
	use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Auditable;

	// audit
	protected bool $auditEnabled = true;
	protected static $auditIncludeSnapshot = true;
	protected static $auditCriticalEvents = ['created', 'updated', 'deleted','force_deleted'];
	protected static $auditExclude = ['password'];

	// protected $connection = 'mysql';
	protected $table = 'logins';

	 /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'username',
		// 'email',
		'password',
		'active',
	];

	 /**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
		protected $hidden = [
		'password',
		'remember_token',
	];

	 /**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
	'id' => 'integer',
	'staff_id' => 'integer',
	'active' => 'boolean',
	'email_verified_at' => 'datetime',
	'password' => 'hashed',
];

	/////////////////////////////////////////////////////////////////////////////////////////////////////

	//public function getAuthIdentifierName()
	//{
	//	return 'username';
	//}

	// for password
	//public function getAuthPassword()
	//{
	//	return $this->password;
	//}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation hasMany/hasOne

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsTo
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// custom email reset password in
	// https://laracasts.com/discuss/channels/laravel/how-to-override-the-tomail-function-in-illuminateauthnotificationsresetpasswordphp
	// public function sendPasswordResetNotification($token)
	// {
	// 		$this->notify(new ResetPassword($token));
	// }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
		/**
		 * Get the e-mail address where password reset links are sent.
		 *
		 * @return string
		 */
	public function getEmailForPasswordReset()
	{
		// return $this->email;
		return $this->belongstostaff->email;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// for email Notifiable
	// https://laravel.com/docs/7.x/notifications
	public function routeNotificationForMail($notification)
	{
		// Return email address only...
		// return $this->belongtouser->email;
		return [$this->belongstostaff->email => $this->belongstostaff->name];
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// used for mustVerifyEmail
	/**
	 * Determine if the user has verified their email address.
	 *
	 * @return bool
	 */
	public function hasVerifiedEmail()
	{
		// return ! is_null($this->email_verified_at);
		return ! is_null($this->belongstostaff->email_verified_at);
	}

	/**
	 * Mark the given user's email as verified.
	 *
	 * @return bool
	 */
	public function markEmailAsVerified()
	{
		return $this->belongstostaff->forceFill([
			'email_verified_at' => $this->freshTimestamp(),
		])->save();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// all acl will be done here
	public function isOwner( $id ) {
		if ( auth()->user()->belongstostaff->id == $id ) {
			return true;
		}
	}

	// access for admin of the system
	public function isAdmin()
	{
		$user = auth()->user();

		if (!$user) {
			return false;
		}

		return $user->belongstostaff()
		->where('authorise_id', config('auth.admins.authorise_id'))
		->exists()
		|| in_array($user->id, config('auth.admins.login_ids', []));
	}

	// high management — shared logic for all levels
	protected function checkHighManagement($highManagement, $dept): bool
	{
		$user = auth()->user();

		if (!$user || !$user->belongstostaff) {
			return false;
		}

		if ($user->isAdmin()) {
			return true;
		}

		$userDivId = $user->belongstostaff->div_id;

		$allowedDivisions = Str::contains($highManagement, '|')
		? explode('|', $highManagement)
		: [$highManagement];

		if (!in_array($userDivId, $allowedDivisions)) {
			return false;
		}

		if (strtolower($dept) === 'null') {
			return true;
		}

		$allowedDepartments = Str::contains($dept, '|')
		? explode('|', $dept)
		: [$dept];

		$userDept = $user->belongstostaff
		->belongstomanydepartment()
		->wherePivot('main', 1)
		->first();

		if (!$userDept) {
			return false;
		}

		return in_array($userDept->id, $allowedDepartments);
	}

	public function isHighManagement($highManagement, $dept): bool
	{
		return $this->checkHighManagement($highManagement, $dept);
	}

	public function isHighManagementlvl1($highManagement, $dept): bool
	{
		return $this->checkHighManagement($highManagement, $dept);
	}

	public function isHighManagementlvl2($highManagement, $dept): bool
	{
		return $this->checkHighManagement($highManagement, $dept);
	}

	public function isHighManagementlvl3($highManagement, $dept): bool
	{
		return $this->checkHighManagement($highManagement, $dept);
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
}

