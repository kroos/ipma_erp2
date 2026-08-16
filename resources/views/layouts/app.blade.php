<?php
use \Carbon\Carbon;
$currentYear = Carbon::now()->year;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="" type="image/x-icon" rel="icon" />
	<meta name="description" content="">
	<meta name="keywords" content="erp system, erp" />
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{!! config('app.name') !!}</title>
	<link href="{{ asset('images/logo.png') }}" type="image/x-icon" rel="icon" />
	<!-- Styles -->
	<link href="{{ mix('css/app.css') }}" rel="stylesheet">
	<!-- <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet"> -->
	@stack('styles')
</head>
	<body class="ipma-app d-flex flex-column min-vh-100" data-route="{{ Route::currentRouteName() }}">

		@hasSection('fullscreen')
			<div class="flex-fill d-flex flex-column">
				<div class="container mt-3">
					<div class="col-sm-12 mx-auto">
						@include('layouts.messages')
					</div>
				</div>
				<div class="flex-fill d-flex">
					@yield('fullscreen')
				</div>
			</div>
		@else
			@include('layouts.navigation')

			@auth
				<div class="container-fluid px-3 pt-3">
					<div class="col-sm-12">
						@include('layouts.nav')
					</div>
				</div>
			@endauth

			<div class="container-fluid flex-fill px-3 py-3">

				<div class="col-sm-12 mx-auto">
					@include('layouts.messages')
				</div>

				@hasSection('page-header')
					<div class="page-header">
						@yield('page-header')
					</div>
				@endif

				<main class="col-sm-12 mx-auto">
					@yield('content')
				</main>

			</div>

			<footer class="ipma-footer py-3 mt-auto">
				<div class="container-fluid d-flex flex-column align-items-center gap-1">
					<p class="m-0 fs-6">
						<a href="{{ config('app.url') }}">{{ config('app.name') }}</a> built on Laravel v{{ app()->version() }}
					</p>
					<small class="m-0 fw-lighter text-body-secondary">Made with love by Dhiauddin and Tan</small>
				</div>
			</footer>
		@endif
	</body>

	<script>
	</script>
	<script src="{{ mix('js/app.js') }}"></script>
	<script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
	<script src="{{ asset('js/ckeditor/adapters/jquery.js') }}"></script>
	<script src="{{ asset('js/jquery-chained/jquery.chained.js') }}"></script>
	<script src="{{ asset('js/jquery-chained/jquery.chained.remote.js') }}"></script>
	<script>
		(function($){
			$(document).ready(function(){
				@section('js')
				@show
			});
		})(jQuery);
	</script>
	@stack('scripts')
</html>
