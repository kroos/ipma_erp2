@extends('layouts.app')

@section('content')
<div class="container row">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<h2>Add Customer</h2>
		<form method="POST" action="{{ route('salescustomer.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
			@csrf
			@include('sales.salescustomer._form')
		</form>
	</div>
</div>
@endsection

@section('js')
	@include('sales.salescustomer._js')
@endsection
