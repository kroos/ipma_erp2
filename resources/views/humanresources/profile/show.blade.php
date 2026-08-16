@extends('layouts.app')

@section('content')
<?php
// \Carbon\Carbon used bare (Carbon::) in the attendance table below
use \Carbon\Carbon;
?>

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
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}</dd>
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
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Confirm Date</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}</dd>
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
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($spouse->dob)->format('d F Y') }}</dd>
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
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($children->dob)->format('d F Y') }}</dd>
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
				@foreach ($attendance as $attend)
				<?php
				$in = NULL;
				$break = NULL;
				$resume = NULL;
				$out = NULL;
				$work_hour = NULL;
				$leave_id = NULL;
				$leave_form = NULL;
				$leave_type = NULL;

				// working hour, daytype, outstation, overtime pre-computed by StaffProfileService
				$company_hour = $companyHours[$attend->id] ?? null;
				$daytype = $daytypes[$attend->id] ?? null;
				$outstation = $outstations[$attend->id] ?? null;
				$overtime = $overtimes[$attend->id] ?? null;

				if ($attend->in != NULL && $attend->in != '00:00:00') {
					$in = Carbon::parse($attend->in)->format('h:i a');
				}

				if ($attend->in > $company_hour->time_start_am) {
					$color_in = "color:red";
				} else {
					$color_in = NULL;
				}

				if ($attend->break != NULL && $attend->break != '00:00:00') {
					$break = Carbon::parse($attend->break)->format('h:i a');
				}

				if ($attend->break < $company_hour->time_end_am) {
					$color_break = "color:red";
				} else {
					$color_break = NULL;
				}

				if ($attend->resume != NULL && $attend->resume != '00:00:00') {
					$resume = Carbon::parse($attend->resume)->format('h:i a');
				}

				if ($attend->resume > $company_hour->time_start_pm) {
					$color_resume = "color:red";
				} else {
					$color_resume = NULL;
				}

				if ($attend->out != NULL && $attend->out != '00:00:00') {
					$out = Carbon::parse($attend->out)->format('h:i a');
				}

				if ($attend->out < $company_hour->time_end_pm) {
					$color_out = "color:red";
				} else {
					$color_out = NULL;
				}

				if ($attend->time_work_hour != NULL && $attend->time_work_hour != '00:00:00') {
					$work_hour = Carbon::parse($attend->time_work_hour)->format('H:i');
				}

				if ($attend->leave_id != NULL && $attend->leave_id != '') {
					$leaveInfo = $leaveInfos[$attend->id] ?? null;

					$leave_id = $leaveInfo['id'] ?? null;

					$leave_form = $leaveInfo['form'] ?? null;

					$leave_type = $leaveInfo['type'] ?? null;
				}
				?>

				<tr>
					<td class="text-center">
						{{ Carbon::parse($attend->attend_date)->format('j M Y') }}
					</td>
					<td class="text-center">
						{{ $daytype->daytype }}
					</td>
					<td class="text-center">
						<span style="{{ $color_in }}">{{ $in }}</span>
					</td>
					<td class="text-center">
					<span style="{{ $color_break }}">{{ $break }}</span>
					</td>
					<td class="text-center">
					<span style="{{ $color_resume }}">{{ $resume }}</span>
					</td>
					<td class="text-center">
					<span style="{{ $color_out }}">{{ $out }}</span>
					</td>
					<td class="text-center">
						{{ $work_hour }}
					</td>
					<td class="text-center" data-bs-toggle="tooltip" data-bs-html="true" title="{{ $overtime }}">
						{{ $overtime }}
					</td>
					<td class="text-center">
						@if ($leave_id != NULL)
						<a href="{{ route('leave.show', $leave_id) }}" target="_blank">
							{{ $leave_form }}
						</a>
						@endif
					</td>
					<td class="text-center">
						{{ $leave_type }}
					</td>
					<td class="text-truncate" style="max-width: 1px;" data-bs-toggle="tooltip" data-bs-html="true" title="{{ $attend->attend_remark }}">
						{{ $attend->attend_remark }}
					</td>
					<td class="text-truncate" style="max-width: 120px;" data-bs-toggle="tooltip" data-bs-html="true" title="{{ $outstation }}">
						{{ $outstation }}
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
						<?php $leaves = $annualMap[$al->year] ?? collect(); ?>
						@if($leaves->count())
						<table class="table table-hover table-sm">
							<thead>
								<tr>
									<th>Leave ID</th>
									<th>Duration</th>
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								@foreach($leaves as $key => $leave)
									<tr>
										<td>
											<a href="{{ route('leave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
										</td>
										<td>
											{{ $leave->period_day }} day/s
											<?php $total += $leave->period_day; ?>
										</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr>
									<td>Total</td>
									<td>{{ $total }} day/s</td>
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
						<?php $leaves = $mcMap[$al->year] ?? collect(); ?>
						@if($leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									<?php $total = 0; ?>
									@foreach($leaves as $key => $leave)
										<tr>
											<td>
												<a href="{{ route('leave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
												<?php $total += $leave->period_day; ?>
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<td>Total</td>
										<td>{{ $total }} day/s</td>
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
						<?php $leaves = $maternity; ?>
						@if($leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									<?php $total = 0; ?>
									@foreach($leaves as $key => $leave)
										<tr>
											<td>
												<a href="{{ route('leave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
												<?php $total += $leave->period_day; ?>
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<td>Total</td>
										<td>{{ $total }} day/s</td>
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
				<tr>
					<td>{{ \Carbon\Carbon::parse($al->date_start)->format('j M Y') }}</td>
					<td>{{ \Carbon\Carbon::parse($al->date_end)->format('j M Y') }}</td>
					<td>{{ $al->belongstocustomer?->customer }}</td>
					<td>{{ $al->reason }}</td>
					<td>{{ $al->leave_total }}</td>
					<td>{{ $al->leave_utilize }}</td>
					<td>{{ $al->leave_balance }}</td>
					<td class="table-responsive">
						<?php $leaves = $replacementMap[$al->id] ?? collect(); ?>
						@if($leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									<?php $total = 0; ?>
									@foreach($leaves as $key => $leave)
										<tr>
											<td>
												<a href="{{ route('leave.show', $leave->id) }}" target="_blank">
													HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}
												</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
												<?php $total += $leave->period_day; ?>
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<th>Total</th>
										<th>{{ $total }} day/s</th>
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
	<div class="table-responsive">
	<?php $dur = 0; ?>
	@if($leavesupls->count())
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
						<a href="{{ route('leave.show', $leavesupl->id) }}" target="_blank">HR9-{{ str_pad( $leavesupl->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leavesupl->leave_year }}</a>
					</td>
					<td class="text-center align-middle">{{ $leaveTypeMap[$leavesupl->leave_type_id] ?? '' }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesupl->date_time_start)->format('j M Y') }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesupl->date_time_end)->format('j M Y') }}</td>
					<td class="text-center align-middle">
							{{ $leavesupl->period_day }} day/s
							<?php $dur += $leavesupl->period_day ?>
					</td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th colspan="4" class="text-right">Total :</th>
					<th class="text-center">{{ $dur }} day/s</th>
				</tr>
			</tfoot>
		</table>
	@endif
	</div>

	<p>&nbsp;</p>
	<h4>Medical Certificate Unpaid Leave</h4>
	<div class="table-responsive">
	<?php $durm = 0; ?>
	@if($leavesmcs->count())
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
						<a href="{{ route('leave.show', $leavesmc->id) }}" target="_blank">HR9-{{ str_pad( $leavesmc->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leavesmc->leave_year }}</a>
					</td>
					<td class="text-center align-middle">{{ $leaveTypeMap[$leavesmc->leave_type_id] ?? '' }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesmc->date_time_start)->format('j M Y') }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesmc->date_time_end)->format('j M Y') }}</td>
					<td class="text-center align-middle">
							{{ $leavesmc->period_day }} day/s
							<?php $durm += $leavesmc->period_day ?>
					</td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th colspan="4" class="text-right">Total :</th>
					<th class="text-center">{{ $durm }} day/s</th>
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

