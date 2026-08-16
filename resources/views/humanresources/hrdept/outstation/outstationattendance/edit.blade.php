@extends('layouts.app')

@section('content')
<div class="col-sm-12 row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Add Staff For Outstation Attendance</h4>
	<div class="col-sm-12 row">
		<form method="POST" action="{{ route('hroutstationattendance.update', $hroutstationattendance->id) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
			@csrf
			@method('PATCH')

		<div class="form-group row m-3 @error('date_attend') has-error @enderror">
			<label for="date" class="col-form-label col-sm-4">Attend Date : </label>
			<div class="col-sm-8" style="position:relative;">
				<input type="text" name="date_attend" value="{{ old('date_attend', $hroutstationattendance->date_attend) }}" id="date" class="form-control form-control-sm col-sm-12 @error('date_attend') is-invalid @enderror" placeholder="Date Attend" readonly>
			</div>
		</div>

		<div class="form-group row m-3 @error('outstation_id') has-error @enderror">
			<label for="loc" class="col-form-label col-sm-4">Location : </label>
			<div class="col-sm-8">
				<input type="text" name="outstation_id" value="{{ $hroutstationattendance->belongstooutstation->belongstocustomer->customer }}" id="loc" class="form-control form-control-sm col-sm-5 @error('outstation_id') is-invalid @enderror" readonly>
			</div>
		</div>

		<div class="form-group row m-3 @error('staff_id') is-invalid @enderror">
			<label for="staff" class="col-form-label col-sm-4">Staff : </label>
			<div class="col-sm-8">
				<input type="text" name="staff_id" value="{{ old('staff_id', $hroutstationattendance->belongstostaff->name) }}" id="staff" class="form-control form-control-sm col-sm-5 @error('staff_id') is-invalid @enderror" readonly>
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('in') ? 'has-error' : Null }}">
			<label for="in" class="col-form-label col-sm-4">In : </label>
			<div class="col-sm-8" style="position:relative;">
				<input type="text" name="in" value="{{ old('in', $hroutstationattendance->in) }}" id="in" class="form-control form-control-sm col-sm-12 @error('in') is-invalid @enderror" placeholder="In">
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('out') ? 'has-error' : Null }}">
			<label for="out" class="col-form-label col-sm-4">Out : </label>
			<div class="col-sm-8" style="position:relative;">
				<input type="text" name="out" value="{{ old('out', $hroutstationattendance->out) }}" id="out" class="form-control form-control-sm col-sm-12 @error('out') is-invalid @enderror" placeholder="Out">
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('in') ? 'has-error' : Null }}">
			<label for="remarks" class="col-form-label col-sm-4">Remarks : </label>
			<div class="col-sm-8">
				<textarea name="remarks" id="remarks" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror">{{ old('remarks', $hroutstationattendance->remarks) }}</textarea>
			</div>
		</div>

		<div class="offset-sm-4 col-sm-8">
			<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
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
		staff_id: @json($hroutstationattendance->staff_id),
	},
	errors: @json($errors->toArray()),
};
@endsection
