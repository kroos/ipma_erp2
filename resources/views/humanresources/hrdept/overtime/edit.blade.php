@extends('layouts.app')

@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Edit Staff Overtime</h4>
	<form method="POST" action="{{ route('overtime.update', $overtime) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@method('PATCH')

	<div class="row justify-content-center">
		<div class="col-sm-6 gy-1 gx-1 align-items-start">

			<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
				<label for="rel" class="col-form-label col-sm-4">Staff : </label>
				<div class="col-auto">
					<select name="staff_id" id="rel" class="form-select form-select-sm @error('staff_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($staffs as $id => $label)
							<option value="{{ $id }}" {{ ($overtime->staff_id == $id)?'selected':NULL }}>{{ $label }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
				<label for="nam" class="col-form-label col-sm-4">Date Overtime : </label>
				<div class="col-auto">
					<input type="text" name="ot_date" value="{{ old('ot_date', $overtime->ot_date) }}" id="nam" class="form-control form-control-sm col-sm-12 @error('ot_date') is-invalid @enderror" placeholder="Date Overtime">
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('overtime_range_id') ? 'has-error' : '' }}">
				<label for="mar" class="col-form-label col-sm-4">Overtime : </label>
				<div class="col-auto">
					<select name="overtime_range_id" id="mar" class="form-select form-select-sm col-auto @error('overtime_range_id') is-invalid @enderror" placeholder="Marital Status">
						<option value="">Please choose</option>
						@foreach($overtimeRanges as $id => $label)
							<option value="{{ $id }}" {{ ($id == old('overtime_range_id', $overtime->overtime_range_id))?'selected':NULL }}>{{ $label }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
				<label for="rem" class="col-form-label col-sm-4">Remarks : </label>
				<div class="col-sm-6">
					<textarea name="remark" id="rem" class="form-control form-control-sm col-sm-12 @error('remark') is-invalid @enderror">{{ old('remark', $overtime->remark) }}</textarea>
				</div>
			</div>

	<div class="offset-4 mb-6">
		<button type="submit" class="btn btn-sm btn-outline-secondary">Update Staff Overtime</button>
	</div>

	</form>
</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
	editId: @json(isset($overtime) ? $overtime->id : null),
};
@endsection
