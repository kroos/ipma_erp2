@extends('layouts.app')
@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Generate Payslip Excel Setting</h4>

	<div class="table-responsive">
		<table class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Description</th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody>
				@foreach($settings as $setting)
					<tr>
						<td>{{ $setting->description }}</td>
						<td>
							<input type="number" id="{{ $setting->id }}_setting" step="0.25" name="value" class="col-auto form-control form-control-sm" placeholder="Value" value="{{ $setting->value }}" data-id="{{ $setting->id }}">
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
		update: '{{ url('attendancepayslipexcelsetting/update') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
