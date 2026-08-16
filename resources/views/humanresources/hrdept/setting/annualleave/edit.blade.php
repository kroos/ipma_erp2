@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Edit Annual Leave Entitlement Year {{ $annualleave->year }} for {{ $annualleave->belongstostaff->name }}</h4>
	<form method="POST" action="{{ route('annualleave.update', $annualleave) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@method('PATCH')
		@include('humanresources.hrdept.setting.annualleave._form')
	</form>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.setting.annualleave._js')
@endsection
