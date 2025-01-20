<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
//use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
//use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
//use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRDisciplinary extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'hr_disciplinary';

  	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstosupervisor(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'supervisor_id');
	}

	public function belongstooptdisciplinaryaction(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptDisciplinaryAction::class, 'disciplinary_action_id');
	}

	public function belongstooptviolation(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptViolation::class, 'violation_id');
	}

	public function belongstooptinfractions(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptInfractions::class, 'infraction_id');
	}
}


