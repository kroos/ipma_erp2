<?php
namespace App\Models\HumanResources;

// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// load column name attribute
use Illuminate\Database\Eloquent\Casts\Attribute;

// load helper
use Illuminate\Support\Str;

// load sluggable
// use Cviebrock\EloquentSluggable\Sluggable;
// use Cviebrock\EloquentSluggable\SluggableScopeHelpers;

class HRLeave extends Model
{
	use SoftDeletes/*, Sluggable*/;
	// protected $connection = '';
	// protected $table = '';
	// protected $primaryKey = '';
	// public $incrementing = false;
	// protected $keyType = '';
	// const CREATED_AT = '';
	// const UPDATED_AT = '';
	// protected $rememberTokenName = '';

	protected $table = 'hr_leaves';

	protected $casts = [
    'id' => 'integer',
    'leave_no' => 'integer',
    'leave_year' => 'integer',
    'staff_id' => 'integer',
    'leave_type_id' => 'integer',
    'leave_cat' => 'integer',
    'half_type_id' => 'integer',
    'date_time_start' => 'datetime',
    'date_time_end' => 'datetime',
    'reason' => 'string',
    'period_day' => 'float',
    'hardcopy' => 'boolean',
    'leave_status_id' => 'integer',
];

protected $fillable = [
    'leave_no',
    'leave_year',
    'staff_id',
    'leave_type_id',
    'leave_cat',
    'half_type_id',
    'date_time_start',
    'date_time_end',
    'reason',
    'period_day',
    'period_time',
    'softcopy',
    'hardcopy',
    'leave_status_id',
    'verify_code',
    'remarks'
];

// public function sluggable(): array
// {
// 	return [
// 		'slug' => ['source' => 'UniqueColumnName']
// 	];
// }

// public function getRouteKeyName()
// {
// 	return 'slug';
// }

//////////////////////////////////////////////////////////////////////
/// protected function setColumnNameAttribute($value)
/// {
///     $this->attributes['ColumnName'] = ucwords(Str::lower($value));
/// }

//////////////////////////////////////////////////////////////////////
/// relationship

	public function hasmanyleaveamend(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveAmend::class, 'leave_id');
	}

	public function hasmanyleaveapprovalbackup(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalBackup::class, 'leave_id');
	}

	public function hasmanyleaveapprovalsupervisor(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalSupervisor::class, 'leave_id');
	}

	public function hasmanyleaveapprovalhod(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalHOD::class, 'leave_id');
	}

	public function hasmanyleaveapprovaldir(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalDirector::class, 'leave_id');
	}

	public function hasmanyleaveapprovalhr(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalHR::class, 'leave_id');
	}

	public function hasmanyattendance(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRAttendance::class, 'leave_id');
	}

	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstooptleavetype(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptLeaveType::class, 'leave_type_id');
	}

	public function belongstooptleavestatus(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptLeaveStatus::class, 'leave_status_id');
	}

	public function belongstomanyleaveannual(): BelongsToMany
	{
		return $this->BelongsToMany(\App\Models\HumanResources\HRLeaveAnnual::class, 'pivot_leave_annuals', 'leave_id', 'hr_leave_annual_id');
	}

	public function belongstomanyleavemc(): BelongsToMany
	{
		return $this->BelongsToMany(\App\Models\HumanResources\HRLeaveMC::class, 'pivot_leave_mc', 'leave_id', 'hr_leave_mc_id');
	}

	public function belongstomanyleavematernity(): BelongsToMany
	{
		return $this->BelongsToMany(\App\Models\HumanResources\HRLeaveMaternity::class, 'pivot_leave_maternities', 'leave_id', 'hr_leave_maternity_id');
	}

	public function belongstomanyleavereplacement(): BelongsToMany
	{
		return $this->BelongsToMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'pivot_leave_replacements', 'leave_id', 'hr_leave_replacement_id');
	}

	public function hasoneleaveapprovalbackup(): HasOne
	{
		return $this->HasOne(\App\Models\HumanResources\HRLeaveApprovalBackup::class, 'leave_id');
	}

	public function hasoneleaveapprovalsupervisor(): HasOne
	{
		return $this->HasOne(\App\Models\HumanResources\HRLeaveApprovalSupervisor::class, 'leave_id');
	}

	public function hasoneleaveapprovalhod(): HasOne
	{
		return $this->HasOne(\App\Models\HumanResources\HRLeaveApprovalHOD::class, 'leave_id');
	}

	public function hasoneleaveapprovaldir(): HasOne
	{
		return $this->HasOne(\App\Models\HumanResources\HRLeaveApprovalDirector::class, 'leave_id');
	}

	public function hasoneleaveapprovalhr(): HasOne
	{
		return $this->HasOne(\App\Models\HumanResources\HRLeaveApprovalHR::class, 'leave_id');
	}

}