@extends('layouts.app')
@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Appraisal Setting</h4>

	<div class="table-responsive">
		<table class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th>Value 1</th>
					<th>Value 2</th>
					<th>Value 3</th>
				</tr>
			</thead>
			<tbody>
				@foreach(\App\Models\HumanResources\HRAppraisalSetting::all() as $k)
					<tr>
						<td>{{ $k->name }}</td>
						<td>{{ nl2br($k->description) }}</td>
						<td>
							<input type="number" id="{{ $k->id }}_setting" step="0.5" name="value1" class="form-control form-control-sm" placeholder="Value1" value="{{ $k->value1 }}" data-id="{{ $k->id }}">
						</td>
						<td>
							@if($k->id == 2 || $k->id == 3)
								<input type="number" id="{{ $k->id }}2_setting" step="0.5" name="value2" class="form-control form-control-sm" placeholder="Value2" value="{{ $k->value2 }}" data-id="{{ $k->id }}">
							@endif
						</td>
						<td>
							@if($k->id == 2 || $k->id == 3)
								<input type="number" id="{{ $k->id }}3_setting" step="0.5" name="value3" class="form-control form-control-sm" placeholder="Value3" value="{{ $k->value3 }}" data-id="{{ $k->id }}">
							@endif
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
		appraisalsettingupdate: '{{ url('appraisalsetting/update') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
