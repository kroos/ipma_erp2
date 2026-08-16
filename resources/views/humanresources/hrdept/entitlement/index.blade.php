@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>{{ $config['title'] }}</h4>

	@if($config['variant'] === 'entitlement')
		@foreach($rows as $year => $yearRows)
			<div class="col-sm-12 table-responsive row m-3">
				<table class="table table-hover table-sm" id="active" style="font-size:12px">
					<thead>
						<tr>
							<th class="text-center" colspan="{{ count($config['columns']) }}">{{ $config['title'] }} ({{ $year }}) for Active Staff</th>
						</tr>
						<tr>
							@foreach($config['columns'] as $column)
								<th>{!! $column !!}</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						@foreach($yearRows['active'] as $row)
							<tr>
								<td>{{ $row['username'] }}</td>
								<td>{{ $row['name'] }}</td>
								<td>{{ $row['leave'] }} day/s</td>
								<td>{{ $row['adjustment'] }} day/s</td>
								<td>{{ $row['utilize'] }} day/s</td>
								<td>{{ $row['balance'] }} day/s</td>
								<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $row['remarks'] }}">
									{{ Str::limit($row['remarks'], 10, ' >') }}
								</td>
								<td class="table-responsive">
									@include('humanresources.hrdept.entitlement._leaves', ['leaves' => $row['leaves'], 'total' => $row['leaves_total']])
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<div class="col-sm-12 table-responsive row m-3">
				<table class="table table-hover table-sm" id="inactive" style="font-size:12px">
					<thead>
						<tr>
							<th class="text-center" colspan="{{ count($config['columns']) }}">{{ $config['title'] }} ({{ $year }}) For Inactive Staff</th>
						</tr>
						<tr>
							@foreach($config['columns'] as $column)
								<th>{!! $column !!}</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						@foreach($yearRows['inactive'] as $row)
							<tr>
								<td>{{ $row['username'] }}</td>
								<td>{{ $row['name'] }}</td>
								<td>{{ $row['leave'] }} day/s</td>
								<td>{{ $row['adjustment'] }} day/s</td>
								<td>{{ $row['utilize'] }} day/s</td>
								<td>{{ $row['balance'] }} day/s</td>
								<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $row['remarks'] }}">
									{{ Str::limit($row['remarks'], 10, ' >') }}
								</td>
								<td class="table-responsive">
									@include('humanresources.hrdept.entitlement._leaves', ['leaves' => $row['leaves'], 'total' => $row['leaves_total']])
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endforeach
	@elseif($config['variant'] === 'upl')
		<div class="col-sm-12 table-responsive row m-3">
			@foreach($rows as $year => $staffGroups)
				<table class="table table-hover table-sm" id="active" style="font-size:12px">
					<thead>
						<tr>
							<th class="text-primary" colspan="{{ count($config['columns']) }}">{{ $config['table_title'] }} {{ $year }}</th>
						</tr>
						@foreach($staffGroups as $staffGroup)
							<tr>
								<th class="text-success" colspan="{{ count($config['columns']) }}">{{ $config['table_title'] }} {{ $year }} For {{ $staffGroup['username'] }} {{ $staffGroup['staff_name'] }}</th>
							</tr>
							<tr>
								@foreach($config['columns'] as $column)
									<th>{!! $column !!}</th>
								@endforeach
							</tr>
					</thead>
						@foreach($staffGroup['items'] as $item)
							<tbody>
								<tr>
									<td>{{ $item['username'] }}</td>
									<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $item['name'] }}">
										{{ Str::words($item['name'], 3, ' >') }}
									</td>
									<td>
										<a href="{{ route('hrleave.show', $item['id']) }}" target="_blank">
											HR9-{{ str_pad($item['leave_no'], 5, '0', STR_PAD_LEFT) }}/{{ $item['leave_year'] }}
										</a>
									</td>
									<td>{{ $item['leave_type_code'] }}</td>
									<td>{{ $item['period_day'] }} day/s</td>
									<td>{{ $item['from'] }}</td>
									<td>{{ $item['to'] }}</td>
									<td @if($item['reason']) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $item['reason'] }}" @endif>
										{{ Str::limit($item['reason'], 10, ' >') }}
									</td>
								</tr>
							</tbody>
						@endforeach
						<tfoot>
							<tr>
								<th colspan="3"></th>
								<th>Total</th>
								<th>{{ $staffGroup['total'] }} day/'s</th>
								<th colspan="3"></th>
							</tr>
						</tfoot>
						@endforeach
				</table>
			@endforeach
		</div>
	@elseif($config['variant'] === 'replacement')
		<div class="col-sm-12 table-responsive row">
			@foreach($rows as $year => $staffGroups)
				<table class="table table-hover table-sm text-wrap" id="active" style="font-size:12px">
					<thead>
						<tr>
							<th class="text-center text-success" colspan="{{ count($config['columns']) }}">{{ $config['title'] }} ({{ $year }})</th>
						</tr>
						@foreach($staffGroups as $staffGroup)
							<tr>
								<th colspan="{{ count($config['columns']) }}">&nbsp;</th>
							</tr>
							<tr>
								<th class="text-primary" colspan="{{ count($config['columns']) }}">{{ $config['title'] }} ({{ $year }}) for {{ $staffGroup['username'] }} {{ $staffGroup['staff_name'] }}</th>
							</tr>
							<tr>
								@foreach($config['columns'] as $column)
									<th>{!! $column !!}</th>
								@endforeach
							</tr>
					</thead>
					<tbody>
						@foreach($staffGroup['items'] as $item)
							<tr>
								<td>{{ $item['username'] }}</td>
								<td>{{ $item['name'] }}</td>
								<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $item['reason'] }}">
									{{ Str::limit($item['reason'], 10, ' >') }}
								</td>
								<td>{{ $item['customer'] }}</td>
								<td>{{ $item['leave_total'] }} day/s</td>
								<td>{{ $item['leave_utilize'] }} day/s</td>
								<td>{{ $item['leave_balance'] }} day/s</td>
								<td @if($item['remarks']) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $item['remarks'] }}" @endif>
									{{ Str::limit($item['remarks'], 10, ' >') }}
								</td>
								<td class="table-responsive">
									@include('humanresources.hrdept.entitlement._leaves', ['leaves' => $item['leaves'], 'total' => $item['leaves_total']])
								</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr>
							<th colspan="5"></th>
							<th class="text-primary">Total</th>
							<th class="text-primary">{{ $staffGroup['total'] }} day/s</th>
							<th colspan="2"></th>
						</tr>
					</tfoot>
					@endforeach
				</table>
			@endforeach
		</div>
	@endif
</div>
@endsection

@section('js')
window.data = {
	route: {},
	url: {},
	old: {},
};
@endsection
