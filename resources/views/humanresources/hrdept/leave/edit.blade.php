@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-leave-edit container row align-items-start justify-content-center">
	<div class="col-sm-12">
		@include('humanresources.hrdept.navhr')
		<h4>Leave Edit</h4>
		<div class="table-container">
			<div class="table">
				<div class="table-row header">
					<div class="table-cell" style="width: 40%; background-color: #99ff99;">IPMA INDUSTRY SDN.BHD.</div>
					<div class="table-cell" style="width: 60%; background-color: #e6e6e6;">LEAVE APPLICATION FORM</div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top" style="width: 25%;">STAFF ID : {{ @$login->username }}</div>
					<div class="table-cell-top" style="width: 75%;">NAME : {{ @$staff->name }}</div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top" style="width: 25%;">LEAVE NO : {{ $hrleave->leave_ref }}</div>
					<div class="table-cell-top" style="width: 60%;">DATE : {{ @$date_start }} - {{ @$date_end }} </div>
					<div class="table-cell-top" style="width: 25%;">TOTAL : {{ @$total_leave }} </div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top text-wrap" style="width: 45%;">LEAVE TYPE : {{ $hrleave->belongstooptleavetype->leave_type_code }} ({{ $hrleave->belongstooptleavetype->leave_type }})</div>
					<div class="table-cell-top text-wrap" style="width: 55%;">REASON : {{ $hrleave->reason }} </div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top text-wrap" style="width: 60%;">BACKUP : {{ @$backup_name }}</div>
					<div class="table-cell-top" style="width: 40%;">DATE APPROVED : {{ @$approved_date }} </div>
				</div>
			</div>		@if($hrremarksattendance)
		<div class="table">
			@foreach($hrremarksattendance as $key => $valueble)
				<div class="table-row">
					<div class="table-cell-top" style="width: 100%;">REMARKS FROM ATTENDANCE : {{ $valueble->remarks }}<br/>HR REMARKS FROM ATTENDANCE : {{ $valueble->hr_remarks }}</div>
				</div>
			@endforeach
		</div>
		@endif





			<div class="table">
				<div class="table-row">
					<div class="table-cell-top text-center" style="width: 100%; background-color: #ffcc99; font-size: 18px;">SIGNATURE / APPROVALS</div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					@for ($a = 1; $a <= $count; $a++)
						@if ($supervisor_no==$a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">SUPERVISOR</div>
						@elseif ($hod_no == $a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">HOD</div>
						@elseif ($director_no == $a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">DIRECTOR</div>
						@elseif ($hr_no == $a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">HR</div>
						@endif
					@endfor
				</div>
			</div>

			<div class="table">
				<div class="table-row" style="height: 50px;">
					@for ($a = 1; $a <= $count; $a++)
						@if ($supervisor_no==$a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$supervisor->belongstostaff->name }}</div>
						@elseif ($hod_no == $a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hod->belongstostaff->name }}</div>
						@elseif ($director_no == $a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$director->belongstostaff->name }}</div>
						@elseif ($hr_no == $a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hr->belongstostaff->name }}</div>
						@endif
					@endfor
				</div>
				<div class="table-row">
					@for ($a = 1; $a <= $count; $a++)
						@if ($supervisor_no==$a)
							<div class="table-cell-top1 text-center">{{ @$supervisor->updated_at }}</div>
						@elseif ($hod_no == $a)
							<div class="table-cell-top1 text-center">{{ @$hod->updated_at }}</div>
						@elseif ($director_no == $a)
							<div class="table-cell-top1 text-center">{{ @$director->updated_at }}</div>
						@elseif ($hr_no == $a)
							<div class="table-cell-top1 text-center">{{ @$hr->updated_at }}</div>
						@endif
					@endfor
				</div>
			</div>
		</div>
	</div>

	<p>&nbsp;</p>

	<div class="col-sm-12 row justify-content-center align-items-start">
		<form method="POST" action="{{ route('hrleave.update', $hrleave->id) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@method('PATCH')
		<h5>Edit Leave Application</h5>

		<div class="form-group row m-1 @error('leave_id') has-error @enderror">
			<label for="leave_id" class="col-sm-4 col-form-label">Leave Type : </label>
			<div class="col-sm-8">
				<select name="leave_type_id" id="leave_id" class="form-select form-select-sm @error('leave_type_id') is-invalid @enderror">
					<option value="">Please choose</option>
					@foreach($leaveTypes as $k => $v)
					<option value="{{ $k }}" {{ ($hrleave->leave_type_id == $k)?'selected':NULL }}>{{ $v }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="form-group row m-1 @error('reason') has-error @enderror">
			<label for="reason" class="col-sm-4 col-form-label">Reason : </label>
			<div class="col-sm-8">
				<textarea name="reason" id="reason" class="w-100 form-control form-control-sm @error('reason') is-invalid @enderror">{{ old('reason', $hrleave->reason) }}</textarea>
			</div>
		</div>

		<div id="wrapper" class="m-1">
		</div>

		<div class="form-group row m-1 @error('amend_note') has-error @enderror">
		<label for="amend_note" class="col-sm-4 col-form-label">Amend Note : </label>
			<div class="col-sm-8">
				<textarea name="amend_note" id="amend_note" class="w-100 form-control form-control-sm @error('amend_note') is-invalid @enderror">{{ old('amend_note', $hrleave->amend_note) }}</textarea>
			</div>
		</div>

		<div class="form-group m-1 row">
			<div class="col-sm-8 offset-sm-4">
				<button type="submit" class="btn btn-sm btn-outline-secondary">Submit Application</button>
			</div>
		</div>
	</form>
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
		leaveType: '{{ route('leaveType.leaveType') }}',
		unavailabledate: '{{ route('leavedate.unavailabledate') }}',
		unblockhalfdayleave: '{{ route('unblockhalfdayleave.unblockhalfdayleave') }}',
		timeleave: '{{ route('leavedate.timeleave') }}',
		backupperson: '{{ route('backupperson') }}',
	},
	url: {
	},
	ownerId: {{ $hrleave->belongstostaff->id }},
	staffId: {{ $hrleave->staff_id }},
	dateTimeStartYmd: '{{ $dateTimeStartYmd }}',
	dateTimeStartHis: '{{ $dateTimeStartHis }}',
	userneedbackup: {{ $userneedbackup ?? 0 }},
	backup: {{ $backup ? 'true' : 'false' }},
	setHalfDayMC: {{ $setHalfDayMC ?? 0 }},
	replacement: @json($replacement),
	replacementSelected: @json($replacementSelected),
	staffOptions: @json($staffOptions),
	hrleave: @json($hrleaveFormData),
	old: @json(old()),
	errors: @json($errors->toArray()),
};
@endsection

@section('nonjquery')

@endsection