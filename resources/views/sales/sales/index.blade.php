@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Customer Order &nbsp; <a href="{{ route('sale.create') }}" class="btn btn-sm btn-outline-secondary" > <span class="mdi mdi-point-of-sale"></span>Add Order </a></h2>
			<table class="table table-sm table-hover m-3" id="sales" style="font: 12px roboto-flex;">
				<thead>
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>Customer</th>
						<th>Delivery Date</th>
						<th>Special Request</th>
						<th>Urgency</th>
						<th>Approved By</th>
						<th>Send Date</th>
						<th>Amend</th>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
					@foreach($sales as $sale)
						<tr>
						<td>{{ $sale->sale_ref }}</td>
						<td>{{ $sale->date_order_fmt }}</td>
							<td @if($sale->belongstocustomer?->customer) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $sale->belongstocustomer?->customer }}" @endif>
								{{ Str::limit($sale->belongstocustomer?->customer, 10, ' >') }}
							</td>
							<td>{{ $sale->delivery_fmt }}</td>
							<td @if($sale->special_request) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $sale->special_request }}" @endif>
								{{ Str::limit($sale->special_request, 10, ' >') }}
							</td>
							<td>{!! ($sale->urgency==1)?'<i class="fa-regular fa-circle-check fa-beat fa-1x"></i>':'<i class="fa-regular fa-circle-xmark fa-beat fa-1x"></i>' !!}</td>							<td>
								{!! $sale->approved_html !!}
							</td>
							<td>
								{!! $sale->send_html !!}
							</td>
							<td @if($sale->amend) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $sale->amend }}" @endif>
								<p class="mb-2"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#amend_{{$sale->id}}"><i class="fa-solid fa-hammer fa-beat"></i></button></p>
								{{ $sale->amend ? Str::limit($sale->amend, 7, '>>') : null }}
								<div class="modal modal-lg fade" id="amend_{{ $sale->id}}" tabindex="-1" aria-labelledby="Amend_{{ $sale->sale_ref }}" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered">
										<div class="modal-content">
											<div class="modal-header">
												<h1 class="modal-title fs-5" id="Amend_{{ $sale->sale_ref }}">Modal title</h1>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<form method="POST" action="{{ route('saleamend', $sale) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
													@csrf
													@method('PATCH')
												<div class="form-group row m-2 {{ $errors->has('amend') ? 'has-error' : '' }}">
													<label for="nam" class="col-form-label col-sm-4">Amendment : </label>
													<div class="col-sm-8">
														<textarea name="amend" id="nam" class="form-control form-control-sm col-sm-12 @error('amend') is-invalid @enderror" placeholder="Amendment">{{ old('amend', @$sale->amend) }}</textarea>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
												<button type="submit" class="btn btn-sm btn-primary">Save changes</button>
											</div>
												</form>
										</div>
									</div>
								</div>

							</td>
							<td>
								{!! $sale->actions_html !!}
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
window.data = {
	route: {
	},		url: {
			saleapproved: '{{ url('api/saleapproved') }}',
			salesend: '{{ url('api/salesend') }}',
		},
	old: {
	},
};

@endsection
