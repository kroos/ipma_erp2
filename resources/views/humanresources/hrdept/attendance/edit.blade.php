@extends('layouts.app')

@section('content')
<?php
// day_type, tcms, staff, login, time_work_hour, leaves, leave now provided by AttendanceController

$time_start_am = $time_start_am ?? '';
$time_end_am = $time_end_am ?? '';
$time_start_pm = $time_start_pm ?? '';
$time_end_pm = $time_end_pm ?? '';
?>

<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<div class="d-flex justify-content-center align-items-start">
		<div class="col-md-7">

			<form method="POST" action="{{ route('attendance.update', $attendance->id) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
				@csrf
				@method('PATCH')
				<input type="hidden" name="staff_id" value="<?php echo $staff->id; ?>">

				<h5>Attendance Edit</h5>

				<div class="row mt-3"></div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="id" class="col-form-label">ID : </label>
					</div>
					<div class="col-md-9">
						<input type="text" name="id" value="{{ $login->username }}" id="id" class="form-control form-control-sm" readonly>
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="name" class="col-form-label">Name : </label>
					</div>
					<div class="col-md-9">
						<input type="text" name="name" value="{{ $staff->name }}" id="name" class="form-control form-control-sm" readonly>
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="date" class="col-form-label">Date : </label>
					</div>
					<div class="col-md-9">
						<input type="text" name="attend_date" value="{{ old('attend_date', $attendance->attend_date) }}" id="date" class="form-control form-control-sm @error('attend_date') is-invalid @enderror" readonly>
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="daytype_id" class="col-form-label">Day Type : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('daytype_id') ? 'has-error' : '' }}">
						<select name="daytype_id" id="daytype_id" class="form-select form-select-sm @error('daytype_id') is-invalid @enderror">
							<option value="">Please choose</option>
							@foreach($day_type as $k => $v)
							<option value="{{ $k }}" {{ ($k == $attendance->daytype_id)?'selected':NULL }}>{{ $v }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="attendance_type_id" class="col-form-label">Cause : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('attendance_type_id') ? 'has-error' : '' }}">
						<select name="attendance_type_id" id="attendance_type_id" class="form-select form-select-sm @error('attendance_type_id') is-invalid @enderror">
							<option value="">Please choose</option>
							@foreach($tcms as $k => $v)
							<option value="{{ $k }}" {{ ($k == $attendance->attendance_type_id)?'selected':NULL }}>{{ $v }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="leave_id" class="col-form-label">Leave : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('leave_id') ? 'has-error' : '' }}">
						<select name="leave_id" id="leave_id" class="form-select form-select-sm @error('leave_id') is-invalid @enderror">
							<option value="">Please choose</option>
							@foreach($leave as $k => $v)
							<option value="{{ $k }}" {{ ($k == $attendance->leave_id)?'selected':NULL }}>{{ $v }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="in" class="col-form-label">In : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('in') ? 'has-error' : '' }}" style="position: relative;">
						<input type="text" name="in" value="{{ old('in', $attendance->in) }}" id="in" class="form-control form-control-sm @error('in') is-invalid @enderror" placeholder="In">
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="break" class="col-form-label">Break : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('break') ? 'has-error' : '' }}" style="position: relative;">
						<input type="text" name="break" value="{{ old('break', $attendance->break) }}" id="break" class="form-control form-control-sm @error('break') is-invalid @enderror" placeholder="Break">
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="from1" class="col-form-label">Resume : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('resume') ? 'has-error' : '' }}" style="position: relative;">
						<input type="text" name="resume" value="{{ old('resume', $attendance->resume) }}" id="resume" class="form-control form-control-sm @error('resume') is-invalid @enderror" placeholder="Resume">
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="out" class="col-form-label">Out : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('out') ? 'has-error' : '' }}" style="position: relative;">
						<input type="text" name="out" value="{{ old('out', $attendance->out) }}" id="out" class="form-control form-control-sm @error('out') is-invalid @enderror" placeholder="Out">
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="time_work_hour" class="col-form-label">Duration : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('time_work_hour') ? 'has-error' : '' }}" style="position: relative;">
						<input type="text" name="time_work_hour" value="{{ old('time_work_hour', $attendance->time_work_hour) }}" id="time_work_hour" class="form-control form-control-sm @error('time_work_hour') is-invalid @enderror" placeholder="Duration">
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="remarks" class="col-form-label">Remarks : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('remark') ? 'has-error' : '' }}">
						<input type="text" name="remarks" value="{{ old('remarks', $attendance->remarks) }}" id="remarks" class="form-control form-control-sm @error('remarks') is-invalid @enderror" placeholder="Remarks">
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						<label for="hr_remarks" class="col-form-label">HR Remarks : </label>
					</div>
					<div class="col-md-9 {{ $errors->has('hr_remark') ? 'has-error' : '' }}">
						<input type="text" name="hr_remarks" value="{{ old('hr_remarks', $attendance->hr_remarks) }}" id="hr_remarks" class="form-control form-control-sm @error('hr_remarks') is-invalid @enderror" placeholder="HR Remarks">
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-3">
						&nbsp;
					</div>
					<div class="col-md-9 form-check {{ $errors->has('exception') ? 'has-error' : '' }}">
						<input type="checkbox" name="exception" value="1" id="exception" class="form-check-input @error('exception') is-invalid @enderror" {{ ($attendance->exception)?'checked':NULL }}>
						<label for="exception" class="form-check-label">Exception</label>
					</div>
				</div>

				<div class="row mt-4">
					<div class="text-center">
						<button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
					</div>
				</div>

			</form>

			<div class="row mt-4 text-center">
				<a href="{{ url()->previous() }}">
					<button class="btn btn-sm btn-outline-secondary">Back</button>
				</a>
			</div>

		</div>
	</div>
</div>
@endsection


@section('js')
window.data = {
	timeStartAm: '{{ $time_start_am }}',
	timeEndAm: '{{ $time_end_am }}',
	timeStartPm: '{{ $time_start_pm }}',
	timeEndPm: '{{ $time_end_pm }}',
};
@endsection
