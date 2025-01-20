<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HROvertimeRange extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'hr_overtime_ranges';
	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship
	public function hasmanyovertime(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HROvertime::class, 'attendance_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
}


