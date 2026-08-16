@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-overtime-create col-sm-12 row">
	@include('humanresources.hrdept.navhr')

	<h4 class="align-items-start">Add Overtime Staff</h4>

	<form method="POST" action="{{ route('overtime.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf

	<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
		<label for="rel" class="col-form-label col-sm-2">Staff : </label>
		<div class="col-md-10">
			<div class="scrollable-div">

				@if($staffs->count())
				@foreach($staffs as $k)
				<div class="form-check mb-1 g-3">
					<input class="form-check-input @error('staff_id.*') is-invalid @enderror" name="staff_id[]" type="checkbox" value="{{ $k->staffID }}" id="staff_{{ $k->staffID }}">
					<label class="form-check-label" for="staff_{{ $k->staffID }}">{{ $k->username }} - {{ $k->name }}</label>
				</div>
				@endforeach
				@endif
			</div>
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('overtime_range_id') ? 'has-error' : '' }}">
		<label for="mar" class="col-form-label col-sm-2">Overtime : </label>
		<div class="col-sm-10">
			<select name="overtime_range_id" id="mar" class="form-select form-select-sm col-sm-8 @error('overtime_range_id') is-invalid @enderror" placeholder="Please Select"></select>
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
		<label for="nam" class="col-form-label col-sm-2">Date Overtime : </label>
		<div class="col-md-10" style="position: relative">
			<input type="text" name="ot_date" value="{{ old('ot_date') }}" id="nam" class="form-control form-control-sm col-sm-12 @error('ot_date') is-invalid @enderror" placeholder="Date Overtime">
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
		<label for="rem" class="col-form-label col-sm-2">Remarks : </label>
		<div class="col-sm-10">
			<textarea name="remark" id="rem" class="form-control form-control-sm col-sm-12 @error('remark') is-invalid @enderror">{{ old('remark') }}</textarea>
		</div>
	</div>

	<div class="form-group row mb-3 g-3 p-2">
		<div class="col-sm-10 offset-sm-2">
			<button type="submit" class="btn btn-sm btn-outline-secondary">Add Overtime Staff</button>
		</div>
	</div>

	</form>
</div>
@endsection

@section('js')
window.data = {
	route: {
		samelocationstaff: '{{ route('samelocationstaff') }}',
		overtimerange: '{{ route('overtimerange') }}',
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
	staffId: @json($staffId),
	hasMinDate: @json($hasMinDate),
	editId: @json(isset($overtime) ? $overtime->id : null),
};
@endsection
