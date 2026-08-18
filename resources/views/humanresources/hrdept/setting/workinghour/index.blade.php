@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Working Hour &nbsp; <a href="{{ route('workinghour.create') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Generate Working Hour For Next Year</a> </h4>
	<table class="table table-hover table-sm" id="workinghour" style="font-size:12px">
	@foreach($years as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="9">Normal Working Hours ({{ $tp['year'] }})</th>
			</tr>
			<tr>
				<th>Year</th>
				<th>Time Start AM</th>
				<th>Time End AM</th>
				<th>Time Start PM</th>
				<th>Time End PM</th>
				<th>Effective Date From</th>
				<th>Effective Date To</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach($tp['normal'] as $t)
			<tr>
				<td>{{ $t['year'] }}</td>
				<td>{{ $t['time_start_am'] }}</td>
				<td>{{ $t['time_end_am'] }}</td>
				<td>{{ $t['time_start_pm'] }}</td>
				<td>{{ $t['time_end_pm'] }}</td>
				<td>{{ $t['effective_date_start'] }}</td>
				<td>{{ $t['effective_date_end'] }}</td>
				<td>{{ $t['remarks'] }}</td>
				<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('workinghour.edit', $t['id']) }}"><i class="far fa-edit"></i></a></td>
			</tr>
		@endforeach
		</tbody>
		<thead>
			<tr>
				<th class="text-center" colspan="9">Maintenance Working Hours {{ $tp['year'] }}</th>
			</tr>
			<tr>
				<th>Year</th>
				<th>Time Start AM</th>
				<th>Time End AM</th>
				<th>Time Start PM</th>
				<th>Time End PM</th>
				<th>Effective Date From</th>
				<th>Effective Date To</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach($tp['maintenance'] as $t)
			<tr>
				<td>{{ $t['year'] }}</td>
				<td>{{ $t['time_start_am'] }}</td>
				<td>{{ $t['time_end_am'] }}</td>
				<td>{{ $t['time_start_pm'] }}</td>
				<td>{{ $t['time_end_pm'] }}</td>
				<td>{{ $t['effective_date_start'] }}</td>
				<td>{{ $t['effective_date_end'] }}</td>
				<td>{{ $t['remarks'] }}</td>
				<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('workinghour.edit', $t['id']) }}"><i class="far fa-edit"></i></a></td>
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
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
