@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Add Holiday Calendar</h4>
	<form method="POST" action="{{ route('holidaycalendar.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@include('humanresources.hrdept.setting.holidaycalendar._form')
	</form>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.setting.holidaycalendar._js')
@endsection
