@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\HRHolidayCalendar;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Holiday Calendar &nbsp; <a href="{{ route('holidaycalendar.create') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Add Holiday</a> </h4>

	<table class="table table-hover table-sm" id="holidaycalendar" style="font-size:12px">
	@foreach(HRHolidayCalendar::groupByRaw('YEAR(date_start)')->selectRaw('YEAR(date_start) as year')->orderBy('date_start', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="6">&nbsp;</th>
			</tr>
			<tr>
				<th class="text-center" colspan="6">Holiday Calendar ({{ $tp->year }})</th>
			</tr>
			<tr>
				<th>From</th>
				<th>To</th>
				<th>Holiday</th>
				<th>Duration</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRHolidayCalendar::whereYear('date_start', $tp->year)->orderBy('date_start', 'ASC')->get() as $t)
			<tr>
				<td>{{ Carbon::parse($t->date_start)->format('D, j M Y') }}</td>
				<td>{{ Carbon::parse($t->date_end)->format('D, j M Y') }}</td>
				<td>{{ $t->holiday }}</td>
				<td>{{ Carbon::parse($t->date_start)->daysUntil($t->date_end, 1)->count() }} day/s</td>
				<td>{{ $t->remarks }}</td>
				<td>
					<div class="btn-group btn-group-sm" role="group">
						<a class="btn btn-sm btn-outline-secondary" href="{{ route('holidaycalendar.edit', $t->id) }}"><i class="far fa-edit"></i></a>
						<button type="button" class="btn btn-sm btn-outline-secondary text-danger holiday-delete" data-id="{{ $t->id }}"><i class="far fa-trash-alt"></i></button>
					</div>
				</td>
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
		holidaycalendar: '{{ url('holidaycalendar') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
