@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-staff-show col-sm-12 row justify-content-center align-items-start">
	@include('humanresources.hrdept.navhr')
	<h4 class="align-items-center">Profile {{ $staff->name }}
		<a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-sm btn-outline-secondary">
			<i class="bi bi-person-lines-fill"></i> Edit
		</a>
		&nbsp;
		<a href="#" class="btn btn-sm btn-outline-secondary text-danger deactivate" data-id="{{ $staff->id }}">
			<i class="bi bi-person-fill-dash"></i> Deactivate
		</a>
	</h4>
	<div class="d-flex flex-column align-items-center text-center">
		<img class="rounded-5 m-3" src="{{ asset('storage/user_profile/' . $staff->image) }}" style="width: 200px;">
		<span class="font-weight-bold">{{ $staff->name }}</span>			<span class="font-weight-bold">{{ $login?->username }}</span>
	</div>
	<div>&nbsp;</div>
	<div class="col-sm-6 row">
		<dl class="row">
			<dt class="col-sm-5">Name :</dt>
			<dd class="col-sm-7">{{ $staff->name }}</dd>
			<dt class="col-sm-5">Identity Card/Passport :</dt>
			<dd class="col-sm-7">{{ $staff->ic }}</dd>
			<dt class="col-sm-5">Religion :</dt>
			<dd class="col-sm-7">{{ $staff->belongstoreligion?->religion }}</dd>
			<dt class="col-sm-5">Gender :</dt>
			<dd class="col-sm-7">{{ $staff->belongstogender?->gender }}</dd>
			<dt class="col-sm-5">Race :</dt>
			<dd class="col-sm-7">{{ $staff->belongstorace?->race }}</dd>
			<dt class="col-sm-5">Nationality :</dt>
			<dd class="col-sm-7">{{ $staff->belongstonationality?->country }}</dd>
			<dt class="col-sm-5">Marital Status :</dt>
			<dd class="col-sm-7">{{ $staff->belongstomaritalstatus?->marital_status }}</dd>
			<dt class="col-sm-5">Email :</dt>
			<dd class="col-sm-7">{{ $staff->email }}</dd>
			<dt class="col-sm-5">Address :</dt>
			<dd class="col-sm-7">{{ $staff->address }}</dd>
			<dt class="col-sm-5">Place of Birth :</dt>
			<dd class="col-sm-7">{{ $staff->place_of_birth }}</dd>
			<dt class="col-sm-5">Mobile :</dt>
			<dd class="col-sm-7">{{ $staff->mobile }}</dd>
			<dt class="col-sm-5">Phone :</dt>
			<dd class="col-sm-7">{{ $staff->phone }}</dd>
			<dt class="col-sm-5">Date of Birth :</dt>
			<dd class="col-sm-7">{{ $dob_fmt }}</dd>
			<dt class="col-sm-5">CIMB Account :</dt>
			<dd class="col-sm-7">{{ $staff->cimb_account }}</dd>
			<dt class="col-sm-5">EPF Account :</dt>
			<dd class="col-sm-7">{{ $staff->epf_account }}</dd>
			<dt class="col-sm-5">Income Tax No :</dt>
			<dd class="col-sm-7">{{ $staff->income_tax_no }}</dd>
			<dt class="col-sm-5">SOCSO No :</dt>
			<dd class="col-sm-7">{{ $staff->socso_no }}</dd>
			<dt class="col-sm-5">Weight :</dt>
			<dd class="col-sm-7">{{ $staff->weight }} kg</dd>
			<dt class="col-sm-5">Height :</dt>
			<dd class="col-sm-7">{{ $staff->height }} cm</dd>
			<dt class="col-sm-5">Date Join :</dt>
			<dd class="col-sm-7">{{ $join_fmt }}</dd>
			<dt class="col-sm-5">Date Confirmed :</dt>
			<dd class="col-sm-7">{{ $confirmed_fmt }}</dd>
			<dt class="col-sm-5">Spouse :</dt>
			<dd class="col-sm-7">
				<div class="table-responsive">
					@if($spouses->count())
					<table class="table table-sm table-hover" style="font-size:12px;">
						<thead>
							<tr>
								<th>Name</th>
								<th>Phone</th>
							</tr>
						</thead>
						<tbody>
							@foreach($spouses as $sp)
							<tr>
								<td>{{ $sp->spouse }}</td>
								<td>{{ $sp->phone }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					@endif
				</div>
			</dd>
			<dt class="col-sm-5">Children :</dt>
			<dd class="col-sm-7">
				<div class="table-responsive">
					@if($childrens->count())
					<table class="table table-sm table-hover" style="font-size:12px;">
						<thead>
							<tr>
								<th>Name</th>
								<th>Age</th>
								<th>Tax Exemption (%)</th>
							</tr>
						</thead>
						<tbody>
							@foreach($childrens as $sc)
							<tr>
								<td>{{$sc->children}}</td>
								<td>{{ $sc->age }} year/s</td>
								<td>{{ $sc->belongstotaxexemptionpercentage?->tax_exemption_percentage }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					@endif
				</div>
			</dd>
			<dt class="col-sm-5">Emergency Contact :</dt>
			<dd class="col-sm-7">
				<div class="table-responsive">
					@if($emergencies->count())
					<table class="table table-sm table-hover" style="font-size:12px;">
						<thead>
							<tr>
								<th>Name</th>
								<th>Phone</th>
							</tr>
						</thead>
						<tbody>
							@foreach($emergencies as $sc)
							<tr>
								<td>{{ $sc->contact_person }}</td>
								<td>{{ $sc->phone }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					@endif
				</div>
			</dd>
		</dl>
	</div>

	<div class="col-sm-6 row">
		<dl class="row">
			<dt class="col-sm-4">System Administrator :</dt>
			<dd class="col-sm-8">{{ $staff->belongstoauthorised?->authorise }}</dd>
			<dt class="col-sm-4">Staff Status :</dt>
			<dd class="col-sm-8">{{ $staff->belongstostatus?->status }}</dd>
			<dt class="col-sm-4">Category :</dt>
			<dd class="col-sm-8">{{ $dept?->belongstocategory?->category }}</dd>
			<dt class="col-sm-4">Branch :</dt>
			<dd class="col-sm-8">{{ $dept?->belongstobranch?->location }}</dd>
			<dt class="col-sm-4">Department :</dt>
			<dd class="col-sm-8">{{ $dept?->department }}</dd>
			<dt class="col-sm-4">Leave Approval Flow :</dt>
			<dd class="col-sm-8">{{ $staff->belongstoleaveapprovalflow?->description }}</dd>
			<dt class="col-sm-4">RestDay Group :</dt>
			<dd class="col-sm-8">{{ $staff->belongstorestdaygroup?->group }}</dd>
			<dt class="col-sm-4">Cross Backup To :</dt>
			<dd class="col-sm-8">
				@if($cb->count())
				<ul>
					@foreach($cb as $r)
					<li>{{ $r->name }}</li>
					@endforeach
				</ul>
				@endif
			</dd>
			<dt class="col-sm-4">Cross Backup For :</dt>
			<dd class="col-sm-8">
				@if($cbf->count())
				<ul>
					@foreach($cbf as $rf)
					<li>{{ $rf->name }}</li>
					@endforeach
				</ul>
				@endif
			</dd>
			@if($annualLeaves->count())
			<dt class="col-sm-4">Annual Leave :</dt>
			<dd class="col-sm-8 table-responsive">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">Year</th>
							<th class="text-center align-middle">AL Entitlement</th>
							<th class="text-center align-middle">AL Adjustment</th>
							<th class="text-center align-middle">AL Utilize</th>
							<th class="text-center align-middle">AL Balance</th>
							<th class="text-center align-middle">&nbsp;</th>
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
							<td class="text-center align-middle">
								<a href="{{ route('annualleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</dd>
			@endif
			@if($mcLeaves->count())
			<dt class="col-sm-4">MC Leave :</dt>
			<dd class="col-sm-8 table-responsive">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">Year</th>
							<th class="text-center align-middle">MC Entitlement</th>
							<th class="text-center align-middle">MC Adjustment</th>
							<th class="text-center align-middle">MC Utilize</th>
							<th class="text-center align-middle">MC Balance</th>
							<th class="text-center align-middle">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						@foreach($mcLeaves as $al)
						<tr>
							<td class="text-center align-middle">{{ $al->year }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave_adjustment }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave_utilize }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave_balance }}</td>
							<td class="text-center align-middle">
								<a href="{{ route('mcleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</dd>
			@endif
			@if($staff->gender_id == 2)
			@if($maternityLeaves->count())
			<dt class="col-sm-4">Maternity Leave :</dt>
			<dd class="col-sm-8 table-responsive">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">Year</th>
							<th class="text-center align-middle">Maternity Entitlement</th>
							<th class="text-center align-middle">Maternity Adjustment</th>
							<th class="text-center align-middle">Maternity Utilize</th>
							<th class="text-center align-middle">Maternity Balance</th>
							<th class="text-center align-middle">&nbsp;</th>
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
								<a href="{{ route('maternityleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</dd>
			@endif
			@endif
		</dl>
	</div>

	<div class="col-sm-12 table-responsive">
		<h4>Entitlements</h4>
		<table class="table table-sm table-hover table-bordered" style="font-size: 12px;">
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

	<p>&nbsp;</p>
	<h4 class="align-items-center">Attendance</h4>
	<div class="table-responsive">

		<form method="GET" action="{{ route('staff.show', $staff->id) }}" accept-charset="UTF-8" id="attendanceForm" autocomplete="off">
			<table width="100%">
				<tr>
					<td></td>
					<td width="100px">
						<select name="year" id="year" class="form-select form-select-sm">
							<option value="">Please choose</option>
							@foreach($group_year as $k1 => $v1)
								<option value="{{ $k1 }}" {{ (old('year', $year) == $k1) ? 'selected' : NULL }}>{{ $v1 }}</option>
							@endforeach
						</select>
					</td>
					<td width="5px"></td>
					<td width="80px">
						<select name="month" id="month" class="form-select form-select-sm">
							<option value="">Please choose</option>
							@foreach($group_month as $k1 => $v1)
								<option value="{{ $k1 }}" {{ (old('month', $month) == $k1) ? 'selected' : NULL }}>{{ $v1 }}</option>
							@endforeach
						</select>
					</td>
					<td width="5px"></td>
					<td width="70px">
						<button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
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
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<h4 class="align-items-center">Leave</h4>
	<div class="table-responsive">
		<table id="leave" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th>No</th>
					<th>Type</th>
					<th>Applied Date</th>
					<th>From</th>
					<th>To</th>
					<th>Duration</th>
					<th>Reason</th>
					<th>Status</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<h4>Annual Leave Entitlement</h4>
	@if($annualLeaves->count())
	<div class="table-responsive">
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">AL Entitlement</th>
					<th class="text-center align-middle">AL Adjustment</th>
					<th class="text-center align-middle">AL Utilize</th>
					<th class="text-center align-middle">AL Balance</th>
					<th class="text-center align-middle">Leave</th>
					<th class="text-center align-middle">&nbsp;</th>
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
											<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">{{ $leave->leave_ref }}</a>
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
					<td class="text-center align-middle">
						<a href="{{ route('annualleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
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
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">MC Entitlement</th>
					<th class="text-center align-middle">MC Adjustment</th>
					<th class="text-center align-middle">MC Utilize</th>
					<th class="text-center align-middle">MC Balance</th>
					<th class="text-center align-middle">Leave</th>
					<th class="text-center align-middle">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				@foreach($mcLeaves as $al)
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
												<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">{{ $leave->leave_ref }}</a>
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
					<td class="text-center align-middle">
						<a href="{{ route('mcleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@endif
	</div>

	@if($staff->gender_id == 2)
	<p>&nbsp;</p>
	<h4>Maternity Leave</h4>
	<div class="table-responsive">
		@if($maternityLeaves->count())
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">Maternity Entitlement</th>
					<th class="text-center align-middle">Maternity Adjustment</th>
					<th class="text-center align-middle">Maternity Utilize</th>
					<th class="text-center align-middle">Maternity Balance</th>
					<th class="text-center align-middle">Leave</th>
					<th class="text-center align-middle">&nbsp;</th>
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
												<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">{{ $leave->leave_ref }}</a>
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
					<td class="text-center align-middle">
						<a href="{{ route('maternityleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
	@endif

	<p>&nbsp;</p>
	<h4>Unpaid Leave</h4>
	<div class="table-responsive">
	@if($leavesupls->count())
		<table class="table table-sm table-hover" style="font-size:12px;">
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
						<a href="{{ route('hrleave.show', $leavesupl->id) }}" target="_blank">{{ $leavesupl->leave_ref }}</a>
					</td>
					<td class="text-center align-middle">{{ $leaveTypeMap[$leavesupl->leave_type_id] ?? '' }}</td>
					<td class="text-center align-middle">{{ $leavesupl->from_fmt }}</td>
					<td class="text-center align-middle">{{ $leavesupl->to_fmt }}</td>
					<td class="text-center align-middle">{{ $leavesupl->period_day }} day/s</td>
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
	<div class="table-responsive">
	@if($leavesmcs->count())
		<table class="table table-sm table-hover" style="font-size:12px;">
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
						<a href="{{ route('hrleave.show', $leavesmc->id) }}" target="_blank">{{ $leavesmc->leave_ref }}</a>
					</td>
					<td class="text-center align-middle">{{ $leaveTypeMap[$leavesmc->leave_type_id] ?? '' }}</td>
					<td class="text-center align-middle">{{ $leavesmc->from_fmt }}</td>
					<td class="text-center align-middle">{{ $leavesmc->to_fmt }}</td>
					<td class="text-center align-middle">{{ $leavesmc->period_day }} day/s</td>
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

	<p>&nbsp;</p>
	<h4 class="align-items-center">Replacement Leave</h4>
	<div class="table-responsive">
		<table class="table table-sm table-hover" style="font-size:12px;" id="replacementleave">
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
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	<p>&nbsp;</p>
	<h4 class="align-items-center">Disciplinary</h4>
	<div class="table-responsive">
		<table class="table table-sm table-hover" style="font-size:12px;" id="disc">
			<thead>
				<tr>
					<th>Discipline Action</th>
					<th>Violation</th>
					<th>Reason</th>
					<th>Misconduct Date</th>
					<th>Softcopy</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
		staffattendance: '{{ route('staffattendance') }}',
		staffpercentage: '{{ route('staffpercentage') }}',
		deactivatestaff: '{{ url('api/deactivatestaff') }}',
		discipline: '{{ url('discipline') }}',
		staffindex: '{{ route('staff.index') }}',
	},
	url: {
		attendance: '{{ route('api.staff.attendance', $staff->id) }}',
		leaves: '{{ route('api.staff.leaves', $staff->id) }}',
		replacement: '{{ route('api.staff.replacement', $staff->id) }}',
		disciplinaries: '{{ route('api.staff.discipline', $staff->id) }}',
		staffshow: '{{ route('staff.show', $staff->id) }}',
	},
	old: {
		staffId: '{{ $staff->id }}',
		staffName: '{{ $staff->name }}',
	},
	errors: @json($errors->toArray()),
};
@endsection
