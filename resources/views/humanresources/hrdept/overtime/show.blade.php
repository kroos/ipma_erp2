@extends('layouts.app')

@section('content')
<div class="container">
	@include('humanresources.hrdept.navhr')
	<h4>Show Staff Overtime</h4>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="staff_id" class="col-form-label col-sm-2">Staff ID : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="staff_id" value="{{ old('staff_id', $username) }}" id="staff_id" class="form-control form-control-sm col-sm-12" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="name" class="col-form-label col-sm-2">Name : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="name" value="{{ old('name', $name) }}" id="name" class="form-control form-control-sm col-sm-12" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="ot_date" class="col-form-label col-sm-2">Overtime Date : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="ot_date" value="{{ old('ot_date', $ot_date_fmt) }}" id="ot_date" class="form-control form-control-sm col-sm-12" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="start" class="col-form-label col-sm-2">Overtime Start : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="start" value="{{ old('start', $start_fmt) }}" id="start" class="form-control form-control-sm col-sm-12" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="end" class="col-form-label col-sm-2">Overtime End : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="end" value="{{ old('end', $end_fmt) }}" id="end" class="form-control form-control-sm col-sm-12" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="total_time" class="col-form-label col-sm-2">Duration : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="total_time" value="{{ old('total_time', $total_time) }}" id="total_time" class="form-control form-control-sm col-sm-12" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="assign_staff_id" class="col-form-label col-sm-2">Assign By : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="assign_staff_id" value="{{ old('assign_staff_id', $assign_name) }}" id="assign_staff_id" class="form-control form-control-sm col-sm-12" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
			<label for="remark" class="col-form-label col-sm-2">Remarks : </label>
		</div>
		<div class="col-md-10">
			<textarea name="remark" class="form-control form-control-sm col-sm-12" readonly>{{ old('remark', @$overtime->remark) }}</textarea>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-12 text-center">
			<a href="{{ url()->previous() }}">
				<button class="btn btn-sm btn-outline-secondary">BACK</button>
			</a>
		</div>
	</div>

</div>
@endsection

@section('js')

@endsection

@section('nonjquery')

@endsection
