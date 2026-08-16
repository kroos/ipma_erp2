@extends('layouts.app')

@section('content')
<div class="container row">
@include('humanresources.hrdept.navhr')
	<div class="row justify-content-center">
		<h2>Add Outstation Customer</h2>
		<form method="POST" action="{{ route('outstationcustomer.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
			@csrf
			@include('humanresources.hrdept.outstation.outstationcustomer._form')
		</form>
	</div>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.outstation.outstationcustomer._js')
@endsection