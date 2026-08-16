@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\HRLeaveAnnual;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Annual Leave Entitlement &nbsp; <button type="button" id="genal" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Generate Annual Leave For Next Year</button> </h4>
	<table class="table table-hover table-sm" id="ann" style="font-size:12px">
	@foreach(HRLeaveAnnual::groupBy('year')->select('year')->orderBy('year', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Annual Leave Entitlement ({{ $tp->year }}) for Active Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Annual Leave</th>
				<th>Annual Leave Adjustment</th>
				<th>Annual Leave Utilize</th>
				<th>Annual Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRLeaveAnnual::where('year', $tp->year)->orderBy('year', 'DESC')->get() as $t)
			@if($t->belongstostaff->active == 1)
				<tr>
					<td>{{ $t->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
					<td>{{ $t->belongstostaff->name }}</td>
					<td>{{ $t->annual_leave }} day/s</td>
					<td>{{ $t->annual_leave_adjustment }} day/s</td>
					<td>{{ $t->annual_leave_utilize }} day/s</td>
					<td>{{ $t->annual_leave_balance }} day/s</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('annualleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
				</tr>
			@endif
		@endforeach
		</tbody>
	@endforeach
	</table>
	<p>&nbsp;</p>
	<table class="table table-hover table-sm" style="font-size:12px">
	@foreach(HRLeaveAnnual::groupBy('year')->select('year')->orderBy('year', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Annual Leave Entitlement ({{ $tp->year }}) For Inactive Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Annual Leave</th>
				<th>Annual Leave Adjustment</th>
				<th>Annual Leave Utilize</th>
				<th>Annual Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRLeaveAnnual::where('year', $tp->year)->orderBy('year', 'DESC')->get() as $t)
			@if($t->belongstostaff->active <> 1)
				<tr>
					<td>{{ $t->belongstostaff->hasmanylogin()->first()?->username }}</td>
					<td>{{ $t->belongstostaff->name }}</td>
					<td>{{ $t->annual_leave }} day/s</td>
					<td>{{ $t->annual_leave_adjustment }} day/s</td>
					<td>{{ $t->annual_leave_utilize }} day/s</td>
					<td>{{ $t->annual_leave_balance }} day/s</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('annualleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
				</tr>
			@endif
		@endforeach
		</tbody>
	@endforeach
	</table>
</div>
@endsection

@section('js')
window.data = {
	route: {
		generateannualleave: '{{ route('generateannualleave') }}',
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
