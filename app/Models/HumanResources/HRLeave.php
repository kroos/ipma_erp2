<?php
namespace App\Models\HumanResources;

// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

	protected $casts = [
// 	'is_active' => 'boolean',
// 	'users_id' => 'integer',					// foreign key
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

}