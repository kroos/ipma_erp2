@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Outstation Customer Details &nbsp; <a href="{{ route('outstationcustomer.index') }}" class="btn btn-sm btn-outline-secondary">Back</a></h2>
			<table class="table table-sm table-hover m-3" id="outstationcustomer" style="font: 12px roboto-flex;">
				<tbody>
					<tr>
						<td style="width: 150px;"><strong>Company Name</strong></td>
						<td>{{ $outstationcustomer->customer }}</td>
					</tr>
					<tr>
						<td><strong>Customer Name</strong></td>
						<td>{{ $outstationcustomer->contact }}</td>
					</tr>
					<tr>
						<td><strong>Phone</strong></td>
						<td>{{ $outstationcustomer->phone }}</td>
					</tr>
					<tr>
						<td><strong>Fax</strong></td>
						<td>{{ $outstationcustomer->fax }}</td>
					</tr>
					<tr>
						<td><strong>Address</strong></td>
						<td>{{ $outstationcustomer->address }}</td>
					</tr>
					<tr>
						<td><strong>Latitude</strong></td>
						<td>{{ $outstationcustomer->latitude }}</td>
					</tr>
					<tr>
						<td><strong>Longitude</strong></td>
						<td>{{ $outstationcustomer->longitude }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
	},
	old: {
	},
};

@endsection
