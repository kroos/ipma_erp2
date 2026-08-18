@extends('layouts.app')

@section('content')
<div class="col-sm-12 d-flex flex-column justify-content-center align-items-center my-2 m-0">

	<div class="card my-2">
		<div class="card-header d-flex justify-content-between">
			<h3 class="my-auto">SalesIndex</h3>
			<a href="{{ route('sales-index.create') }}" class="my-auto btn btn-sm btn-outline-primary">
				<i class="fa-solid fa-arrow-up-right-from-square"></i>New Data
			</a>
		</div>
		<div class="card-body">

			<table id="sales-index"></table>

		</div>
	</div>

</div>
@endsection

@section('js')
window.data = {
	route: {
		getSalesIndex: '{{ route('getSalesIndex') }}',
	},
	url: {
		'sales-index': '{{ url('sales-index') }}',
	},
	old: {
	},
};
@endsection
