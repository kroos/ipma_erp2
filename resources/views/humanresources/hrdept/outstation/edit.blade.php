@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Add Staff For Outstation</h4>
  <form method="POST" action="{{ route('outstation.update', $outstation->id) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
  @csrf
  @method('PATCH')

	<div class="form-group row mb-3 @error('staff') has-error @enderror">
		<label for="staff" class="col-sm-2 col-form-label">Outstation Staff : </label>
		<div class="col-md-5">
			<input type="text" name="staff" value="{{ $staffName }}" id="staff" class="form-control form-control-sm col-auto @error('staff') is-invalid @enderror" readonly>
		</div>
	</div>

	<div class="form-group row mb-3 @error('customer_id') has-error @enderror">
		<label for="loc" class="col-sm-2 col-form-label">Location : </label>
		<div class="col-md-8">
			<select name="customer_id" id="loc" class="form-select form-select-sm col-sm-12 @error('customer_id') is-invalid @enderror">
				<option value="">Please choose</option>
				@foreach($c as $k1 => $v1)
					<option value="{{ $k1 }}" {{ (old('customer_id', $outstation->customer_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
				@endforeach
			</select>

		</div>
	</div>

	<div class="form-group row mb-3 @error('supervisor_id') has-error @enderror">
		<label for="supervisor_id" class="col-sm-2 col-form-label">Supervisor : </label>
		<div class="col-md-10" style="position: relative">
			<input type="text" name="date_from" value="{{ old('date_from', $outstation->date_from) }}" id="from" class="form-control form-control-sm col-sm-12 @error('date_from') is-invalid @enderror" placeholder="Date From">
		</div>
	</div>

	<div class="form-group row mb-3 @error('date_to') has-error @enderror">
		<label for="to" class="col-sm-2 col-form-label">To : </label>
		<div class="col-md-10" style="position: relative">
			<input type="text" name="date_to" value="{{ old('date_to', $outstation->date_to) }}" id="to" class="form-control form-control-sm col-sm-12 @error('date_to') is-invalid @enderror" placeholder="To">
		</div>
	</div>

	<div class="form-group row mb-3 @error('remarks') has-error @enderror">
		<label for="rem" class="col-sm-2 col-form-label">Remarks : </label>
		<div class="col-md-10">
			<textarea name="remarks" id="rem" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror">{{ old('remarks', $outstation->remarks) }}</textarea>
		</div>
	</div>

	<div class="form-group row mb-3 g-3 p-2">
		<div class="col-sm-10 offset-sm-2">
			<button type="submit" class="btn btn-sm btn-outline-secondary">Edit Data</button>
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
