@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Attendance Absent Indicator</h4>
	<div class="col-sm-12 row">
		<div id="calendar" class="col-sm-12 m-1"></div>
	</div>
</div>
@endsection

@section('js')
window.data = {
	eventsUrl: '{{ route('attendanceabsentindicator') }}',
};
@endsection
