<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRRestdayCalendar extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'hr_restday_calendars';

protected $casts = [
    'id' => 'integer',
    'date_start' => 'date',
    'date_end' => 'date',
    'friday_date' => 'date',
    'restday_group_id' => 'integer',
    'saturday_date' => 'date',
    'restday' => 'string',
    'remarks' => 'string',
];

	/////////////////////////////////////////////////////////////////////////////////////////
	public function belongstorestdaygroup(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptRestdayGroup::class, 'restday_group_id');
	}
}
