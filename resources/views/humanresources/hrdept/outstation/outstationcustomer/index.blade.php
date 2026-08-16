@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Outstation Customer &nbsp; <a href="{{ route('outstationcustomer.create') }}" class="btn btn-sm btn-outline-secondary"> <span class="mdi mdi-account-plus"></span>Add Customer </a></h2>
			<table class="table table-sm table-hover m-3" id="outstationcustomer" style="font: 12px roboto-flex;">
				<thead>
					<tr>
						<th>ID</th>
						<th>Company</th>
						<th>Customer Name</th>
						<th>Address</th>
						<th>Phone</th>
						<th>Fax</th>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
					@foreach($outstationcustomer as $customer)
					<tr>
						<td>{{ $customer->id }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $customer->customer }}">
							{{ Str::limit($customer->customer, 20, ' >') }}
						</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $customer->contact }}">
							{{ Str::limit($customer->contact, 20, ' >') }}
						</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $customer->address }}">
							{{ Str::limit($customer->address, 20, ' >') }}
						</td>
						<td>{{ $customer->phone }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $customer->fax }}">
							{{ Str::limit($customer->fax, 20, ' >') }}
						</td>
						<td>
							<div class="btn-group btn-group-sm" role="group">
								<a href="{{ route('outstationcustomer.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-eye fa-beat"></i>
								</a>
								<a href="{{ route('outstationcustomer.edit', $customer->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-pen-to-square fa-beat-to-square fa-beat"></i>
								</a>
								<button class="btn btn-sm btn-outline-secondary customer-delete" data-id="{{ $customer->id }}">
									<i class="fa-solid fa-trash-can fa-beat" style="color: red;"></i>
								</button>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.outstation.outstationcustomer._js')
@endsection