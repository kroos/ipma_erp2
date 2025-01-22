<?php
use \Carbon\Carbon;

$currentYear = Carbon::now()->year;
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<!-- <title>Laravel11 Boilerplate</title> -->
	<title>{{ config('app.name', 'Laravel') }}</title>

	<link href="{{ asset('images/logo.png') }}" type="image/x-icon" rel="icon" />

	<meta name="keywords" content="" />

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.bunny.net">
	<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

	<!-- Styles -->
	<link href="{{ URL::asset('css/app.css') }}" rel="stylesheet">
	<!-- Bootswatch Cerulean CSS -->
	<link href="{{ URL::asset('css/bootstrap.css') }}" rel="stylesheet">
	<!-- Livewire CSS -->
	@livewireStyles

</head>
<body class="min-vh-100 row align-items-center justify-content-center mx-auto bg-secondary bg-opacity-75">
	@include('layouts.navbar')

	<div class="container align-self-center m-0">
		<div class="col-sm-12 align-self-center justify-content-center m-0">
			@include('layouts.messages')
		</div>
		<div class="col-sm-12 align-self-center justify-content-center m-0">
			@yield('content')
		</div>
	</div>
	<div class="container align-self-end text-center text-sm text-gray">
		Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
	</div>
</body>
<!-- <script type="module" src="{{ asset('js/fullcalendar/bootstrap5/index.global.js') }}"></script> -->
<!-- <script type="module" src="{{ asset('js/fullcalendar/daygrid/index.global.js') }}"></script> -->
<!-- <script type="module" src="{{ asset('js/fullcalendar/multimonth/index.global.js') }}"></script> -->
<script src="{{ asset('js/fullcalendar/index.global.js') }}"></script>
<!-- <script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script> -->
<!-- <script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script> -->
<script src="{{ asset('js/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('js/ckeditor/adapters/jquery.js') }}"></script>
<script >
	jQuery.noConflict ();
	(function($){
		$(document).ready(function(){
			@section('js')
			@show
		});
	})(jQuery);
</script>
<script>
	@section('nonjquery')
	@show
</script>
@livewireScripts
</html>
