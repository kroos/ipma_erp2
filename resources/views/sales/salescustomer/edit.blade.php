@extends('layouts.app')

@section('content')
<div class="container row">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<h2>Edit Customer</h2>
		<form method="POST" action="{{ route('salescustomer.update', $customer) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
			@csrf
			@method('PATCH')
			@include('sales.salescustomer._form')
		</form>
	</div>
</div>
@endsection

@section('js')
	@include('sales.salescustomer._js')
@endsection
