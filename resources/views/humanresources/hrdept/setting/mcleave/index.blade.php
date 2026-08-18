@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Medical Certificate Leave Entitlement &nbsp; <button type="button" id="genal" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Generate Medical Certificate Leave For Next Year</button> </h4>
	<table class="table table-hover table-sm" id="mcl" style="font-size:12px">
	@foreach($years as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Medical Certificate Leave Entitlement ({{ $tp->year }}) for Active Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Medical Certificate Leave</th>
				<th>Medical Certificate Leave Adjustment</th>
				<th>Medical Certificate Leave Utilize</th>
				<th>Medical Certificate Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach($activeRows[$tp->year] ?? collect() as $t)
				<tr>
					<td>{{ $t->username }}</td>
					<td>{{ $t->name }}</td>
					<td>{{ $t->entitlement_fmt }}</td>
					<td>{{ $t->adjustment_fmt }}</td>
					<td>{{ $t->utilize_fmt }}</td>
					<td>{{ $t->balance_fmt }}</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('mcleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
				</tr>
		@endforeach
		</tbody>
	@endforeach
	</table>
	<p>&nbsp;</p>
	<table class="table table-hover table-sm" style="font-size:12px">
	@foreach($years as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Medical Certificate Leave Entitlement ({{ $tp->year }}) For Inactive Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Medical Certificate Leave</th>
				<th>Medical Certificate Leave Adjustment</th>
				<th>Medical Certificate Leave Utilize</th>
				<th>Medical Certificate Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach($inactiveRows[$tp->year] ?? collect() as $t)
				<tr>
					<td>{{ $t->username }}</td>
					<td>{{ $t->name }}</td>
					<td>{{ $t->entitlement_fmt }}</td>
					<td>{{ $t->adjustment_fmt }}</td>
					<td>{{ $t->utilize_fmt }}</td>
					<td>{{ $t->balance_fmt }}</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('mcleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
				</tr>
		@endforeach
		</tbody>
	@endforeach
	</table>
</div>
@endsection

@section('js')
window.data = {
	route: {
		generatemcleave: '{{ route('generatemcleave') }}',
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
