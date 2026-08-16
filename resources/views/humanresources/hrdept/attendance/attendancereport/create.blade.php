@extends('layouts.app')

@section('content')
<?php
use Illuminate\Database\Eloquent\Builder;
?>

<div class="page-humanresources-hrdept-attendance-attendancereport-create container table-responsive row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Attendance Report</h4>

	<form method="GET" action="{{ route('attendancereport.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
		@csrf
		<div class="row g-3 mb-3">
			<div class="col-auto @error('from') is-invalid @enderror" style="position:relative;">
				<input type="text" name="from" class="form-control form-control-sm" id="from" value="" placeholder="Date From">
			</div>
			<div class="col-auto @error('to') is-invalid @enderror" style="position:relative;">
				<input type="text" name="to" class="form-control form-control-sm" id="to" value="" placeholder="Date To">
			</div>
			<div class="col-auto">
				<input type="submit" class="form-control form-control-sm btn btn-sm btn-outline-secondary" id="to" value="Submit">
			</div>
		</div>
		<div class="g-3 mb-3 py-3 scrollable-div col-sm 5 wrap_checkbox @error('staff_id') is-invalid @enderror">
		</div>
	</form>
</div>
@endsection

@section('js')
window.data = {
	route: {
		staffattendancelist: '{{ route('staffattendancelist') }}',
		branchattendancelist: '{{ route('branchattendancelist') }}',
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
