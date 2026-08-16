@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Edit Maternity Leave Entitlement Year {{ $maternityleave->year }} for {{ $maternityleave->belongstostaff->name }}</h4>
	<form method="POST" action="{{ route('maternityleave.update', $maternityleave) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@method('PATCH')
		@include('humanresources.hrdept.setting.maternityleave._form')
	</form>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.setting.maternityleave._js')
@endsection
