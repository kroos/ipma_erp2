@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-outstation-create col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Add Staff For Outstation</h4>
	<form method="POST" action="{{ route('outstation.store') }}" accept-charset="UTF-8" id="form" class="" autocomplete="off" enctype="multipart/form-data">
		@csrf
	<div class="form-group row mb-3 @error('staff_id') has-error @enderror">
		<div class="col-md-2">
		<label for="staff" class="col-sm-2 col-form-label">Outstation Staff : </label>
		</div>
		<div class="col-md-10">
			<div class="scrollable-div">
				@foreach ($staffs as $staff)
				<p>
					<input type="checkbox" id="staff_id{{ $staff->staffID }}" name="staff_id[]" id="staff" value="{{ $staff->staffID }}">
					<label for="staff_id{{ $staff->staffID }}">{{ $staff->username }} - {{ $staff->name }}</label>
				</p>
				@endforeach
			</div>
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('customer_id') ? 'has-error' : '' }}">
		<label for="loc" class="col-sm-2 col-form-label">Location : </label>
		<div class="col-md-10">
			<select name="customer_id" id="loc" class="form-select form-select-sm col-auto @error('customer_id') is-invalid @enderror">
				<option value="">Please choose</option>
				@foreach($c as $k => $v)
					<option value="{{ $k }}" {{ (old('customer_id') == $k)?'selected':NULL }}>{{ $v }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="form-group row mb-3 @error('date_from') has-error @enderror">
		<label for="from" class="col-sm-2 col-form-label">From : </label>
		<div class="col-md-10" style="position: relative">
			<input type="text" name="date_from" value="{{ old('date_from') }}" id="from" class="form-control form-control-sm col-auto @error('date_from') has-error @enderror">
		</div>
	</div>

	<div class="form-group row mb-3 @error('date_to') has-error @enderror">
		<label for="to" class="col-sm-2 col-form-label">To : </label>
		<div class="col-md-10" style="position: relative">
			<input type="text" name="date_to" value="{{ old('date_to') }}" id="to" class="form-control form-control-sm col-auto @error('date_to') has-error @enderror">
		</div>
	</div>

	<div class="form-group row mb-3 @error('remarks') has-error @enderror">
		<label for="name" class="col-sm-2 col-form-label">Name : </label>
		<div class="col-md-10">
			<textarea name="remarks" id="rem" class="form-control form-control-sm col-auto" placeholder="Remarks">{{ old('remarks') }}</textarea>
		</div>
	</div>

	<div class="form-group row mb-3 g-3 p-2">
		<div class="col-sm-10 offset-sm-2">
			<button type="submit" class="btn btn-sm btn-outline-secondary">Add Data</button>
		</div>
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
	editId: @json(isset($outstation) ? $outstation->id : null),
};
@endsection
