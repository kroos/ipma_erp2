<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;
use App\Models\Staff;
// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OptMaritalStatus extends Model
{
    use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'option_marital_statuses';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasmanystaff(): HasMany
	{
		return $this->hasMany(\App\Models\Staff::class, 'marital_status_id');
	}
}
