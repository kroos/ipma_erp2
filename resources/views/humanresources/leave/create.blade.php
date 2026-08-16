@extends('layouts.app')
@section('content')
<div class="container row align-items-start justify-content-center">
	<div class="table-responsive col-sm-12 m-5">
		<table class="table table-hover table-sm">
			<tbody>
				<tr class="">
					<td rowspan="3" class="text-danger w-25">Attention :</td>
					<td>
						Leave application must be at least <span class="font-weight-bold">THREE (3)</span> days in advance for <strong>"Annual Leave"</strong> and <strong>"Unpaid Leave"</strong>. Otherwise it will be considered as <strong>"Emergency Annual Leave"</strong> or <strong>"Emergency Unpaid Leave"</strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong>"Time-Off"</strong> will consider as a <strong>"Leave"</strong>, if leave period exceed <strong>more than 2 hours</strong>.
					</td>
				</tr>
				<tr>
					<td>
						Application for <strong>"Sick Leave/Medical Certificate (MC)"</strong> or <strong>"Unpaid Medical Certificate (MC-UPL)"</strong> will only be <strong>considered VALID and ELIGIBLE</strong> if a sick/medical certificate is <strong>issued by a REGISTERED government hospital/clinic or panel clinic only.
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- herecomes the hardest part, leave application -->

	<div class="col-sm-12 row">
		<form method="POST" action="{{ route('leave.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" data-toggle="validator" enctype="multipart/form-data">
			@csrf
		<h5 class="text-center">Leave Application</h5>

		<div class="form-group row m-2 {{ $errors->has('leave_type_id') ? 'has-error' : '' }}">
			<label for="leave_id" class="col-sm-4 col-form-label">Leave Type : </label>
			<div class="col-sm-8">
				<select name="leave_type_id" id="leave_id" class="form-control form-control-sm"></select>
			</div>
		</div>

		<div class="form-group row m-2 {{ $errors->has('reason') ? 'has-error' : '' }}">
			<label for="reason" class="col-sm-4 col-form-label">Reason : </label>
			<div class="col-sm-8">
				<textarea name="reason" id="reason" class="form-control form-control-sm" placeholder="Reason">{{ old('reason') }}</textarea>
			</div>
		</div>

		<div id="wrapper">
		</div>

		<div class="form-group row m-2 {{ $errors->has('akuan') ? 'has-error' : '' }}">
			<div class="col-sm-8 offset-sm-4 form-check">
				<input type="checkbox" name="akuan" value="1" id="akuan1" class="form-check-input">
				<label for="akuan1" class="form-check-label p-1 bg-warning text-danger rounded"><p>I hereby confirmed that all details and information filled in are <strong>CORRECT</strong> and <strong>CHECKED</strong> before sending.</p></label>
			</div>
		</div>

		<div class="form-group row mb-3">
			<div class="col-sm-8 offset-sm-4">
				<button type="submit" class="btn btn-sm btn-primary">Submit Application</button>
			</div>
		</div>
		</form>
	</div>
</div>

@endsection
@section('js')

<?php
$user = \Auth::user()->belongstostaff;
$userneedbackup = $user->belongstoleaveapprovalflow?->backup_approval;
$setHalfDayMC = \App\Models\Setting::find(2)->active;
$oi = $user->hasmanyleavereplacement()->where('leave_balance', '<>', 0)->get();
?>

window.data = {
	route: {
		leaveType: '{{ route('leaveType.leaveType') }}',
		unavailabledate: '{{ route('leavedate.unavailabledate') }}',
		unblockhalfdayleave: '{{ route('unblockhalfdayleave.unblockhalfdayleave') }}',
		timeleave: '{{ route('leavedate.timeleave') }}',
		backupperson: '{{ route('backupperson') }}',
	},
	ownerId: {{ \Auth::user()->belongstostaff->id }},
	userneedbackup: {{ $userneedbackup == 1 ? 1 : 0 }},
	setHalfDayMC: {{ $setHalfDayMC == 1 ? 1 : 0 }},
	replacement: @json($oi),
	old: @json(old()),
	errors: @json($errors->toArray()),
};

@endsection

