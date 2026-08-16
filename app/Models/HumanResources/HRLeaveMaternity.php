<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRLeaveMaternity extends Model
{
	use HasFactory, SoftDeletes;

	// protected $connection = 'mysql';
	protected $table = 'hr_leave_maternities';

	protected $fillable = [
	'maternity_leave',
	'maternity_leave_adjustment',
	'maternity_leave_utilize',
	'maternity_leave_balance',
	'remarks',
];

protected $casts = [
	'id' => 'integer',
	'maternity_leave' => 'integer',
	'maternity_leave_adjustment' => 'integer',
	'maternity_leave_utilize' => 'integer',
	'maternity_leave_balance' => 'integer',
	'remarks' => 'string',
	'staff_id' => 'integer',
	'year' => 'integer',
];
}