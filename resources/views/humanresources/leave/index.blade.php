@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	<div class="col-sm-12 table-responsive">
		<table class="table table-hover table-sm table-border">
			<tr>
				<th>Attention</th>
				<td colspan="2">
					<p>
						@if($data['profile_incomplete'])
							<a href="{{ $data['profile_edit_url'] }}" class="btn btn-sm btn-primary"><i class="fa fa-regular fa-user"></i>Profile</a>
						@else
							<a href="{{ $data['leave_create_url'] }}" class="btn btn-sm btn-primary">Leave Application</a>
						@endif
						<br />Please complete your profile before applying your leave. Once completed, please proceed with leave application.
					</p>
				</td>
			</tr>
			@foreach($data['years'] as $year)
			<tr>
				<th colspan="3" class="text-center">Year {{ $year }}</th>
			</tr>
			<tr>
				<th rowspan="2">Annual Leave :</th>
				<td>Initialize :</td>
				<td>{{ $data['entitlements'][$year]['annual_init'] }} days</td>
			</tr>
			<tr>
				<td>Balance:</td>
				<td><span class="{{ ($data['entitlements'][$year]['annual_low']) ? 'text-danger font-weight-bold' : '' }}">{{ $data['entitlements'][$year]['annual_balance'] }} days</span></td>
			</tr>
			<tr>
				<th rowspan="2">Medical Certificate Leave :</th>
				<td>Initialize :</td>
				<td>{{ $data['entitlements'][$year]['mc_init'] }} days</td>
			</tr>
			<tr>
				<td>Balance :</td>
				<td><span class="{{ ($data['entitlements'][$year]['mc_low']) ? 'text-danger font-weight-bold' : '' }}">{{ $data['entitlements'][$year]['mc_balance'] }} days</span></td>
			</tr>
			@if($data['show_maternity'])
			<tr>
				<th rowspan="2">Maternity Leave :</th>
				<td>Initialize :</td>
				<td>{{ $data['entitlements'][$year]['maternity_init'] }} days</td>
			</tr>
			<tr>
				<td>Balance :</td>
				<td><span class="{{ ($data['entitlements'][$year]['maternity_low']) ? 'text-danger font-weight-bold' : '' }}">{{ $data['entitlements'][$year]['maternity_balance'] }} days</span></td>
			</tr>
			@endif
			@endforeach
			<tr>
				<th>Unpaid Leave :</th>
				<td colspan="2">{{ $data['unpaid'] }} days</td>
			</tr>
			@if($data['replacement_visible'])
			<tr>
				<th>Replacement Leave :</th>
				<td colspan="2">{{ $data['replacement'] }} days</td>
			</tr>
			@endif
			@if($data['backup_enabled'])
			<tr>
				<th>Backup Personnel :</th>
				<td colspan="2">
					<ul>
					@foreach($data['backup'] as $bk)
						<li>
							@if($bk['dept'])
								{{ $bk['dept'] }}
							@endif
							<ol>
								@foreach($bk['staff'] as $name)
									<li>{{ $name }}</li>
								@endforeach
							</ol>
						</li>
					@endforeach
					</ul>
				</td>
			</tr>
			@endif
		</table>
	</div>

	<div class="col-sm-12 table-responsive">
		<h4>Leave</h4>
		<p class="card-text text-justify text-lead" id="no-leave-msg" style="display:none">No record for your leave. Click on "Leave Application" to apply a leave.</p>
		<table class="table table-hover table-sm" id="leaves" style="font-size:12px">
			<thead>
				<tr>
					<th rowspan="2">ID</th>
					<th rowspan="2">Date Apply</th>
					<th rowspan="2">Leave</th>
					<th rowspan="2">Reason</th>
					<th colspan="2" >Date/Time Leave</th>
					<th rowspan="2">Period</th>
					<th rowspan="2">Code</th>
					<th rowspan="2">Approval, Remarks and Updated At</th>
					<th rowspan="2">Leave Status</th>
				</tr>
				<tr>
					<th>From</th>
					<th>To</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<div class="col-sm-12 table-responsive" id="backup-approval-wrap" style="display:none">
		<h4>Backup Approval</h4>
		<table class="table table-hover table-sm" id="bapprover" style="font-size:12px">
			<thead>
				<tr>
					<th rowspan="2">Name</th>
					<th rowspan="2">Leave</th>
					<th rowspan="2">Reason</th>
					<th rowspan="2">Date Applied</th>
					<th colspan="2">Date/Time Leave</th>
					<th rowspan="2">Period</th>
					<th rowspan="2">Leave Status</th>
				</tr>
				<tr>
					<th>From</th>
					<th>To</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
@endsection

@section('js')
window.data = {
	url: {
		leavecancel: '{{ url('api/leavecancel') }}',
		leaverapprove: '{{ url('api/leaverapprove') }}',
		myLeaves: '{{ url('api/leave/my-leaves') }}',
	},
};
@endsection
