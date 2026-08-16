@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Customer Details &nbsp; <a href="{{ route('salescustomer.index') }}" class="btn btn-sm btn-outline-secondary">Back</a></h2>
			<table class="table table-sm table-hover m-3" id="salescustomer" style="font: 12px roboto-flex;">
				<tbody>
					<tr>
						<td style="width: 150px;"><strong>Customer</strong></td>
						<td>{{ $customer->customer }}</td>
					</tr>
					<tr>
						<td><strong>Contact</strong></td>
						<td>{{ $customer->contact }}</td>
					</tr>
					<tr>
						<td><strong>Phone</strong></td>
						<td>{{ $customer->phone }}</td>
					</tr>
					<tr>
						<td><strong>Fax</strong></td>
						<td>{{ $customer->fax }}</td>
					</tr>
					<tr>
						<td><strong>Area</strong></td>
						<td>{{ $customer->area }}</td>
					</tr>
					<tr>
						<td><strong>Address</strong></td>
						<td>{{ $customer->address }}</td>
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
