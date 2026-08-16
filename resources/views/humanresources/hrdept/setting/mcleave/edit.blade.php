@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Edit Medical Certificate Leave Entitlement Year {{ $mcleave->year }} for {{ $mcleave->belongstostaff->name }}</h4>
	<form method="POST" action="{{ route('mcleave.update', $mcleave) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@method('PATCH')
		@include('humanresources.hrdept.setting.mcleave._form')
	</form>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.setting.mcleave._js')
@endsection
