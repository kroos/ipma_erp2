<?php

namespace App\Http\Requests\HumanResources;

use Illuminate\Foundation\Http\FormRequest;

class StoreHRLeaveRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array
	{
		return [
			'date_time_start' => 'required|date',
			'date_time_end' => 'sometimes|required|date',
			'reason' => 'required|string',
			'leave_type_id' => 'required|integer',
			'staff_id' => 'sometimes|required|integer',
		];
	}

	public function attributes(): array
	{
		return [
			'date_time_start' => 'Date From',
			'date_time_end' => 'Date To',
			'reason' => 'Leave Reason',
			'leave_type_id' => 'Leave Type',
			'staff_id' => 'Staff',
		];
	}

	public function messages(): array
	{
		return [
			'date_time_start.required' => 'The date from field is required.',
			'date_time_end.required' => 'The date to field is required.',
			'reason.required' => 'The leave reason field is required.',
			'leave_type_id.required' => 'The leave type field is required.',
			'staff_id.required' => 'The staff field is required.',
		];
	}
}