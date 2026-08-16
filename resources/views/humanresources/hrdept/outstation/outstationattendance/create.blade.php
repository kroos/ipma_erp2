@extends('layouts.app')

@section('content')
<div class="col-sm-12 row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Add Staff For Outstation Attendance</h4>
	<div class="col-sm-12 row">
	<form method="POST" action="{{ route('hroutstationattendance.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf

		<div class="form-group row m-3 @error('date_attend') is-invalid @enderror">
			<label for="date" class="col-sm-4 col-form-label">Attend Date : </label>
			<div class="col-sm-8" style="position:relative;">
				<input type="text" name="date_attend" value="{{ old('date_attend') }}" id="date" class="form-control form-control-sm col-sm-12 @error('date_attend') is-invalid @enderror" placeholder="Date Attend">
			</div>
		</div>

		<div class="form-group row m-3 @error('outstation_id') has-error @enderror">
			<label for="loc" class="col-sm-4 col-form-label">Location : </label>
			<div class="col-sm-8">
				<select name="outstation_id" id="loc" class="form-select form-select-sm col-sm-5"></select>
			</div>
		</div>

		<div class="form-group row m-3 @error('staff_id.*') has-error @enderror">
			<label for="staff" class="col-sm-4 col-form-label">Staff : </label>
			<div class="col-sm-8">
				<select name="staff_id[]" id="staff" class="form-select form-select-sm col-sm-5" multiple="multiple"></select>
			</div>
		</div>

		<div class="form-group row m-3 @error('in') has-error @enderror">
			<label for="in" class="col-sm-4 col-form-label">In : </label>
			<div class="col-sm-8" style="position:relative;">
				<input type="text" name="in" value="{{ old('in') }}" id="in" class="form-control form-control-sm col-sm-12 @error('in') is-invalid @enderror" placeholder="In">
			</div>
		</div>

		<div class="form-group row m-3 @error('out') has-error @enderror">
			<label for="out" class="col-sm-4 col-form-label">Out : </label>
			<div class="col-sm-8" style="position:relative;">
				<input type="text" name="out" value="{{ old('out') }}" id="out" class="form-control form-control-sm col-sm-12 @error('out') is-invalid @enderror" placeholder="Out">
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('in') ? 'has-error' : Null }}">
			<label for="remarks" class="col-sm-4 col-form-label">Remarks : </label>
			<div class="col-sm-8">
				<textarea name="remarks" id="remarks" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror">{{ old('remarks') }}</textarea>
			</div>
		</div>

		<div class="offset-sm-4 col-sm-8">
			<button type="submit" class="btn btn-sm btn-outline-secondary">Generate Attendance</button>
		</div>

		</form>
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
		outstationattendancelocation: '{{ route('outstationattendancelocation') }}',
		outstationattendancestaff: '{{ route('outstationattendancestaff') }}',
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
