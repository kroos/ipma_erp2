@extends('layouts.app')

@section('content')

<div class="container row align-items-start justify-content-center">

	<div class="col-sm-2 row">
		<div class="d-flex flex-column align-items-center">
			<img class="rounded-5" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
			<span style="font-size: 18px;"><b>ID: {{ $login?->username }}</b></span>
		</div>
	</div>

	<div class="col-sm-12 row align-items-start justify-content-center">
		<h4>Staff Profile &nbsp; <a href="{{ route('profile.edit', $profile->id) }}" class="btn btn-sm btn-outline-secondary">Change Password</a></h4>
		<div class="col-sm-6">
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $profile->name }}</dd>
				<dt class="col-sm-5">Identity Card/Passport</dt>
				<dd class="col-sm-7">{{ $profile->ic }}</dd>
				<dt class="col-sm-5">Mobile Number</dt>
				<dd class="col-sm-7">{{ $profile->mobile }}</dd>
				<dt class="col-sm-5">Email</dt>
				<dd class="col-sm-7">{{ $profile->email }}</dd>
				<dt class="col-sm-5">Address</dt>
				<dd class="col-sm-7">
					<address>{{ $profile->address }}</address>
				</dd>
				<dt class="col-sm-5">Department</dt>
				<dd class="col-sm-7">{{ $dept?->department }}</dd>
			</dl>
		</div>

		<div class="col-sm-6">
			<dl class="row">
				<dt class="col-sm-5">Category</dt>
				<dd class="col-sm-7">{{ $dept?->belongstocategory?->category }}</dd>
				<dt class="col-sm-5">Saturday Group</dt>
				<dd class="col-sm-7">{{ $profile->belongstorestdaygroup?->group }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ $dob_fmt }}</dd>
				<dt class="col-sm-5">Gender</dt>
				<dd class="col-sm-7">{{ $profile->belongstogender->gender }}</dd>
				<dt class="col-sm-5">Nationality</dt>
				<dd class="col-sm-7">{{ $profile->belongstonationality?->country }}</dd>
				<dt class="col-sm-5">Race</dt>
				<dd class="col-sm-7">{{ $profile->belongstorace?->race }}</dd>
				<dt class="col-sm-5">Religion</dt>
				<dd class="col-sm-7">{{ $profile->belongstoreligion?->religion }}</dd>
				<dt class="col-sm-5">Marital Status</dt>
				<dd class="col-sm-7">{{ $profile->belongstomaritalstatus?->marital_status }}</dd>
				<dt class="col-sm-5">Join Date</dt>
				<dd class="col-sm-7">{{ $join_fmt }}</dd>
				<dt class="col-sm-5">Confirm Date</dt>
				<dd class="col-sm-7">{{ $confirmed_fmt }}</dd>
			</dl>
		</div>
	</div>

	<div class="col-sm-12 row align-items-start justify-content-center mt-3">
		<div class="col-sm-4">
			@if ($emergencies->count())
			<h4>Emergency Contact</h4>
			@foreach ($emergencies as $emergency)
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $emergency->contact_person }}</dd>
				<dt class="col-sm-5">Relationship</dt>
				<dd class="col-sm-7">{{ $emergency->belongstorelationship?->relationship }}</dd>
				<dt class="col-sm-5">Phone Number</dt>
				<dd class="col-sm-7">{{ $emergency->phone }}</dd>
				<dt class="col-sm-5">Address</dt>
				<dd class="col-sm-7">
					<address>{{ $emergency->address }}</address>
				</dd>
			</dl>
			@endforeach
			@endif
		</div>

		<div class="col-sm-4">
			@if ($spouses->count())
			<h4>Spouse</h4>
			@foreach ($spouses as $spouse)
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $spouse->spouse }}</dd>
				<dt class="col-sm-5">Identity Card/Passport</dt>
				<dd class="col-sm-7">{{ $spouse->id_card_passport }}</dd>
				<dt class="col-sm-5">Phone Number</dt>
				<dd class="col-sm-7">{{ $spouse->phone }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ $spouse->dob_fmt }}</dd>
				<dt class="col-sm-5">Profession</dt>
				<dd class="col-sm-7">{{ $spouse->profession }}</dd>
			</dl>
			@endforeach
			@endif
		</div>

		<div class="col-sm-4">
			@if ($childrens->count())
			<h4>Children</h4>
			@foreach ($childrens as $children)
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $children->children }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ $children->dob_fmt }}</dd>
				<dt class="col-sm-5">Gender</dt>
				<dd class="col-sm-7">{{ $children->belongstogender?->gender }}</dd>
				<dt class="col-sm-5">Health Condition</dt>
				<dd class="col-sm-7">{{ $children->belongstohealthstatus?->health_status }}</dd>
				<dt class="col-sm-5">Education Level</dt>
				<dd class="col-sm-7">{{ $children->belongstoeducationlevel?->education_level }}</dd>
			</dl>
			@endforeach
			@endif
		</div>
	</div>

	<p>&nbsp;</p>
	<div class="col-sm-12 table-responsive">
		<h4>Entitlements</h4>
		<table id="ent" class="table table-sm table-hover table-bordered" style="font-size: 12px;">
			<thead>
				<tr>
					<th class="text-center" rowspan="3">Year</th>
					<th class="text-center" colspan="2">Annual Leave (AL)</th>
					<th class="text-center" colspan="2">Medical Certificate Leave (MC)</th>
					<th class="text-center" colspan="2">Maternity Leave (ML)</th>
					<th class="text-center" colspan="2">Replacement Leave (NRL)</th>
					<th class="text-center">Unpaid Leave (UPL)</th>
					<th class="text-center">Medical Certificate Unpaid Leave (MC-UPL)</th>
				</tr>
				<tr>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Total (days)</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-center">{{ now()->format('Y') }}</td>
					<td class="text-center">{{ $annl?->annual_leave_balance }}</td>
					<td class="text-center">{{ $annl?->annual_leave + $annl?->annual_leave_adjustment }}</td>
					<td class="text-center">{{ $mcel?->mc_leave_balance }}</td>
					<td class="text-center">{{ $mcel?->mc_leave + $mcel?->mc_leave_adjustment }}</td>
					<td class="text-center">{{ $matl?->maternity_leave_balance }}</td>
					<td class="text-center">{{ $matl?->maternity_leave + $matl?->maternity_leave_adjustment }}</td>
					<td class="text-center">{{ $replb?->first()?->total }}</td>
					<td class="text-center">{{ $replt?->first()?->total }}</td>
					<td class="text-center">{{ $upal?->first()?->total }}</td>
					<td class="text-center">{{ $mcupl?->first()?->total }}</td>
				</tr>
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<div class="col-sm-12">
		<canvas id="myChart"></canvas>
	</div>

	<p>&nbsp;</p>
	<div id="calendar" class="col-sm-12"></div>

	<?php
	// $group_year + $group_month now provided by StaffProfileService
	?>

	<p>&nbsp;</p>
	<div class="col-sm-12 table-responsive">
		<h4>Attendance</h4>

		<form method="POST" action="{{ route('profile.show', $profile->id) }}" accept-charset="UTF-8" id="form" class="form-horizontal" autocomplete="off" enctype="multipart/form-data">
			@csrf()

		<table width="100%" class="text">
			<tr>
				<td></td>
				<td width="100px">
					<select name="year" id="year" class="form-select form-select-sm form-select">
						<option value="">Please choose</option>
						@if(count($group_year))
							@foreach($group_year as $gy)
								<option value="{{ $gy }}">{{ $gy }}</option>
							@endforeach
						@endif
					</select>
				</td>
				<td width="5px"></td>
				<td width="80px">
					<select name="month" id="month" class="form-select form-select-sm form-select">
						<option value="">Please choose</option>
						@if(count($group_month))
							@foreach($group_month as $gm)
								<option value="{{ $gm }}">{{ $gm }}</option>
							@endforeach
						@endif
					</select>
				</td>
				<td width="5px"></td>
				<td width="70px">
					<button type="submit" class="btn btn-sm btn-outline-secondary">SEARCH</button>
				</td>
			</tr>
		</table>

		</form>

		<table id="attendance" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th class="text-center">Date</th>
					<th class="text-center">Day Type</th>
					<th class="text-center">In</th>
					<th class="text-center">Break</th>
					<th class="text-center">Resume</th>
					<th class="text-center">Out</th>
					<th class="text-center">W/Hour</th>
					<th class="text-center">Overtime</th>
					<th class="text-center">Leave Form</th>
					<th class="text-center">Leave Type</th>
					<th class="text-center">Remark</th>
					<th class="text-center">Outstation</th>
				</tr>
			</thead>
			<tbody>
				{{-- cells pre-built by StaffProfileService::attendanceRows(); HTML spans/tooltips are
				     escaped server-side (e() on user data) — safe for {!! !!} --}}
				@foreach ($attendanceRows as $attend)
				<tr>
					<td class="text-center">
						{{ $attend['date'] }}
					</td>
					<td class="text-center">
						{{ $attend['daytype'] }}
					</td>
					<td class="text-center">
						{!! $attend['in'] !!}
					</td>
					<td class="text-center">
						{!! $attend['break'] !!}
					</td>
					<td class="text-center">
						{!! $attend['resume'] !!}
					</td>
					<td class="text-center">
						{!! $attend['out'] !!}
					</td>
					<td class="text-center">
						{{ $attend['work_hour'] }}
					</td>
					<td class="text-center">
						{!! $attend['overtime'] !!}
					</td>
					<td class="text-center">
						{!! $attend['leave_form'] !!}
					</td>
					<td class="text-center">
						{{ $attend['leave_type'] }}
					</td>
					<td class="text-truncate" style="max-width: 1px;">
						{!! $attend['remark'] !!}
					</td>
					<td class="text-truncate" style="max-width: 120px;">
						{!! $attend['outstation'] !!}
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<h4>Annual Leave Entitlement</h4>
	@if($annualLeaves->count())
	<div class="table-responsive">
		<table id="al" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">AL Entitlement</th>
					<th class="text-center align-middle">AL Adjustment</th>
					<th class="text-center align-middle">AL Utilize</th>
					<th class="text-center align-middle">AL Balance</th>
					<th class="text-center align-middle">Leave</th>
				</tr>
			</thead>
			<tbody>
				@foreach($annualLeaves as $al)
				<tr>
					<td class="text-center align-middle">{{ $al->year }}</td>
					<td class="text-center align-middle">{{ $al->annual_leave }}</td>
					<td class="text-center align-middle">{{ $al->annual_leave_adjustment }}</td>
					<td class="text-center align-middle">{{ $al->annual_leave_utilize }}</td>
					<td class="text-center align-middle">{{ $al->annual_leave_balance }}</td>
					<td class="table-responsive">
						@if($al->leaves->count())
						<table class="table table-hover table-sm">
							<thead>
								<tr>
									<th>Leave ID</th>
									<th>Duration</th>
								</tr>
							</thead>
							<tbody>
								@foreach($al->leaves as $leave)
									<tr>
										<td>
											<a href="{{ route('leave.show', $leave->id) }}" target="_blank">{{ $leave->leave_ref }}</a>
										</td>
										<td>
											{{ $leave->period_day }} day/s
										</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr>
									<td>Total</td>
									<td>{{ $al->total_days }} day/s</td>
								</tr>
							</tfoot>
						</table>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endif

	<p>&nbsp;</p>
	<h4>Medical Certificate Leave</h4>
	<div class="table-responsive">
	@if($mcLeaves->count())
		<table id="mc" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">MC Entitlement</th>
					<th class="text-center align-middle">MC Adjustment</th>
					<th class="text-center align-middle">MC Utilize</th>
					<th class="text-center align-middle">MC Balance</th>
					<th class="text-center align-middle">Leave</th>
				</tr>
			</thead>
			<tbody>				@foreach($mcLeaves as $al)
				<tr>
					<td class="text-center align-middle">{{ $al->year }}</td>
					<td class="text-center align-middle">{{ $al->mc_leave }}</td>
					<td class="text-center align-middle">{{ $al->mc_leave_adjustment }}</td>
					<td class="text-center align-middle">{{ $al->mc_leave_utilize }}</td>
					<td class="text-center align-middle">{{ $al->mc_leave_balance }}</td>
					<td class="text-center align-middle">
						@if($al->leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									@foreach($al->leaves as $leave)
										<tr>
											<td>
												<a href="{{ route('leave.show', $leave->id) }}" target="_blank">{{ $leave->leave_ref }}</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<td>Total</td>
										<td>{{ $al->total_days }} day/s</td>
									</tr>
								</tfoot>
							</table>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@endif
	</div>

	@if($profile->gender_id == 2)
	<p>&nbsp;</p>
	<h4>Maternity Leave</h4>
	<div class="table-responsive">
		@if($maternityLeaves->count())
		<table id="ml" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">Maternity Entitlement</th>
					<th class="text-center align-middle">Maternity Adjustment</th>
					<th class="text-center align-middle">Maternity Utilize</th>
					<th class="text-center align-middle">Maternity Balance</th>
					<th class="text-center align-middle">Leave</th>
				</tr>
			</thead>
			<tbody>
				@foreach($maternityLeaves as $al)
				<tr>
					<td class="text-center align-middle">{{ $al->year }}</td>
					<td class="text-center align-middle">{{ $al->maternity_leave }}</td>
					<td class="text-center align-middle">{{ $al->maternity_leave_adjustment }}</td>
					<td class="text-center align-middle">{{ $al->maternity_leave_utilize }}</td>
					<td class="text-center align-middle">{{ $al->maternity_leave_balance }}</td>
					<td class="text-center align-middle">
						@if($al->leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									@foreach($al->leaves as $leave)
										<tr>
											<td>
												<a href="{{ route('leave.show', $leave->id) }}" target="_blank">{{ $leave->leave_ref }}</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<td>Total</td>
										<td>{{ $al->total_days }} day/s</td>
									</tr>
								</tfoot>
							</table>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
	@endif

		<p>&nbsp;</p>
	<h4 class="align-items-center">Replacement Leave</h4>
	<div class="table-responsive">
		@if($replacementLeaves->count())
		<table id="rpl" class="table table-sm table-hover" style="font-size:12px;" id="replacementleave">
			<thead>
				<tr>
					<th>From</th>
					<th>To</th>
					<th>Location</th>
					<th>Remarks</th>
					<th>Total Day/s</th>
					<th>Leave Utilize</th>
					<th>Leave Balance</th>
					<th>Replacement Leave</th>
				</tr>
			</thead>
			<tbody>
				@foreach($replacementLeaves as $al)
				<tr>						<td>{{ $al->from_fmt }}</td>
						<td>{{ $al->to_fmt }}</td>
					<td>{{ $al->belongstocustomer?->customer }}</td>
					<td>{{ $al->reason }}</td>
					<td>{{ $al->leave_total }}</td>
					<td>{{ $al->leave_utilize }}</td>
					<td>{{ $al->leave_balance }}</td>
					<td class="table-responsive">
						@if($al->leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									@foreach($al->leaves as $leave)
										<tr>
											<td>
												<a href="{{ route('leave.show', $leave->id) }}" target="_blank">
													{{ $leave->leave_ref }}
												</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<th>Total</th>
										<th>{{ $al->total_days }} day/s</th>
									</tr>
								</tfoot>
							</table>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@else
		<p>No Leave Yet</p>
		@endif
	</div>
	<p>&nbsp;</p>
	<h4>Unpaid Leave</h4>
	<div class="table-responsive">			@if($leavesupls->count())
				<table id="upl" class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">ID</th>
							<th class="text-center align-middle">Leave Type</th>
							<th class="text-center align-middle">From</th>
							<th class="text-center align-middle">To</th>
							<th class="text-center align-middle">Duration</th>
						</tr>
					</thead>
					<tbody>
						@foreach($leavesupls as $leavesupl)
						<tr>
							<td class="text-center align-middle">
								<a href="{{ route('leave.show', $leavesupl->id) }}" target="_blank">{{ $leavesupl->leave_ref }}</a>
							</td>
							<td class="text-center align-middle">{{ $leaveTypeMap[$leavesupl->leave_type_id] ?? '' }}</td>
							<td class="text-center align-middle">{{ $leavesupl->from_fmt }}</td>
							<td class="text-center align-middle">{{ $leavesupl->to_fmt }}</td>
							<td class="text-center align-middle">
									{{ $leavesupl->period_day }} day/s
							</td>
						</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr>
							<th colspan="4" class="text-right">Total :</th>
							<th class="text-center">{{ $upl_total }} day/s</th>
						</tr>
					</tfoot>
				</table>
			@endif
	</div>

	<p>&nbsp;</p>
	<h4>Medical Certificate Unpaid Leave</h4>
	<div class="table-responsive">			@if($leavesmcs->count())
				<table id="mcupl" class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">ID</th>
							<th class="text-center align-middle">Leave Type</th>
							<th class="text-center align-middle">From</th>
							<th class="text-center align-middle">To</th>
							<th class="text-center align-middle">Duration</th>
						</tr>
					</thead>
					<tbody>
						@foreach($leavesmcs as $leavesmc)
						<tr>
							<td class="text-center align-middle">
								<a href="{{ route('leave.show', $leavesmc->id) }}" target="_blank">{{ $leavesmc->leave_ref }}</a>
							</td>
							<td class="text-center align-middle">{{ $leaveTypeMap[$leavesmc->leave_type_id] ?? '' }}</td>
							<td class="text-center align-middle">{{ $leavesmc->from_fmt }}</td>
							<td class="text-center align-middle">{{ $leavesmc->to_fmt }}</td>
							<td class="text-center align-middle">
									{{ $leavesmc->period_day }} day/s
							</td>
						</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr>
							<th colspan="4" class="text-right">Total :</th>
							<th class="text-center">{{ $mcupl_total }} day/s</th>
						</tr>
					</tfoot>
				</table>
			@endif
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
		staffattendance: '{{ route('staffattendance') }}',
		staffpercentage: '{{ route('staffpercentage') }}',
	},
	url: {
	},
	old: {
		staffId: '{{ $profile->id }}',
	},
	errors: @json($errors->toArray()),
};
@endsection

