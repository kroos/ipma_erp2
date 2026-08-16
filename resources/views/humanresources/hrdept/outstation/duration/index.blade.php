@extends('layouts.app')
@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Staff Outstation Duration</h4>
	<div id="calendar" class="col-sm-12 m-3"></div>
</div>
@endsection

@section('js')
window.data = {
	route: {
		staffoutstationduration: '{{ route('staffoutstationduration') }}',
	},
	url: {
	},
	old: {
		staff_id: '{{ $staffId ?? '' }}',
	},
	errors: @json($errors->toArray()),
};
@endsection
