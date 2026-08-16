@extends('layouts.app')

@section('content')
<div class="col-sm-12 table-responsive row">
@include('humanresources.hrdept.navhr')
	<h4>Show Attendance</h4>
</div>
@endsection

@section('js')
window.data = {};
@endsection
