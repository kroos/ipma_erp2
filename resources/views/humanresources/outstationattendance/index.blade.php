<?php
use Carbon\Carbon;


?>

@extends('layouts.app')
@section('content')
<div class="container row align-items-start justify-content-center">
	@if ($eligible)
		<h4>Outstation Attendance</h4>

		<div id="map_canvas" class="my-2 vw-50 vh-100">
		</div>

		<form method="POST" action="{{ router('outstationattendance.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" data-toggle="validator" enctype="multipart/form-data">
			@csrf
			<div class="col-sm-12">
				<dl class="row">
					<dt class="col-sm-3">Latitude :</dt>
					<dd class="col-sm-9">
						<input name="latitude" type="text" value="" class="col-sm-auto form-control form-control-sm @error('latitude') is-invalid @enderror" id="lat" aria-describedby="in2" readonly>
						@error('latitude') <div id="in4" class="invalid-feedback">{{ $message }}</div> @enderror
					</dd>
					<dt class="col-sm-3">Longitude :</dt>
					<dd class="col-sm-9">
						<input name="longitude" type="text" value="" class="col-sm-auto form-control form-control-sm @error('longitude') is-invalid @enderror" id="lon" aria-describedby="in3" readonly>
						@error('longitude') <div id="in3" class="invalid-feedback">{{ $message }}</div> @enderror
					</dd>
					<dt class="col-sm-3">Accuracy :</dt>
					<dd class="col-sm-9">
						<input name="accuracy" type="text" value="" class="col-sm-auto form-control form-control-sm @error('accuracy') is-invalid @enderror" id="acc" aria-describedby="in4" readonly>
						@error('accuracy') <div id="in4" class="invalid-feedback">{{ $message }}</div> @enderror
					</dd>
				</dl>
			</div>

			<div class="col-sm-12 my-2 table-responsive">
				<table id="att" class="table table-sm table-hover" style="font-size:12px;">
					<caption>Attendance for today</caption>
					<thead>
						<tr>
							<th>#</th>
							<th>Location</th>
							<th>Date</th>
							<th>In</th>
							<th>Detected Latitude In</th>
							<th>Detected Longitude In</th>
							<th>Out</th>
							<th>Detected Latitude Out</th>
							<th>Detected Longitude Out</th>
						</tr>
					</thead>
					<tbody>
						@foreach($m as $k => $v)
							<tr>
								<td>{{ $v->id }}</td>
								<td>{{ $v->belongstooutstation?->belongstocustomer?->customer }}</td>
								<td>{{ Carbon::parse($v->date_attend)->format('j M Y') }}</td>
								<td>{{ ($v->in)?Carbon::parse($v->in)->format('g:i a'):NULL }}</td>
								<td>{{ $v->in_latitude }}</td>
								<td>{{ $v->in_longitude }}</td>
								<td>{{ ($v->out)?Carbon::parse($v->out)->format('g:i a'):NULL }}</td>
								<td>{{ $v->out_latitude }}</td>
								<td>{{ $v->out_longitude }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<p>Click button below to mark your attendance</p>
			<div class="row my-2 @error('outstation_id') has-error @enderror">
				<label for="outstation" class="col-sm-4 form-label @error('outstation_id') is-invalid @enderror">Location :</label>
				<div class="col-sm-8">
					<select name="outstation_id" id="outstation" class="form-select form-select-sm col-sm-auto @error('outstation_id') is-invalid @enderror" aria-describedby="in1">
						<option value="">Please choose</option>
						@foreach ($locations as $location)
							<option value="{{ $location->id }}">{{ $location->belongstocustomer?->customer }}</option>
						@endforeach
					</select>
					@error('outstation_id') <div id="in1" class="invalid-feedback">{{ $message }}</div> @enderror
				</div>
			</div>
			<div class="row offset-sm-4 col-sm-8">
				<button type="submit" id="in" class="mx-2 col-sm-auto btn btn-sm btn-outline-secondary">Mark Attendance</button>
			</div>
		</form>


	@else
		<h2 class="p-4 m-3 border border-bottom text-center alert alert-danger">Please note, this page can be only use for the outstation personnel. If you are eligible to use this page and mark your attendance, please ask your superior (HR or CS Officer) to assists you by adding your ID into the outstation list.</h2>
	@endif
</div>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
@endsection

@section('js')
window.data = {
	route: {},
	url: {},
	old: {
		eligible: @json($eligible), 
	},
	errors: @json($errors->toArray()),
};
@endsection
