@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Generate Working Hour For A Year</h4>
	<form method="POST" action="{{ route('workinghour.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@include('humanresources.hrdept.setting.workinghour._form')
	</form>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.setting.workinghour._js')
@endsection
