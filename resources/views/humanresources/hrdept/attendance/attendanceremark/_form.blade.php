<div class="col-sm-12 row">
	<div class="col-sm-6">

		<div class="form-group row m-2 @error('staff_id') has-error @enderror">
			<label for="staff" class="col-form-label col-sm-4">Staff : </label>
			<div class="col-sm-8">
				<select name="staff_id" id="staff" class="form-select form-select-sm @error('staff_id') is-invalid @enderror">
					<option value="">Please choose</option>
					@if(old('staff_id', @$attendanceremark?->staff_id))
					<option value="{{ old('staff_id', @$attendanceremark?->staff_id) }}" selected>{{ @$attendanceremark?->belongstostaff?->name }}</option>
					@endif
				</select>
				@error('staff_id')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('date_from') has-error @enderror">
			<label for="from" class="col-form-label col-sm-4">From : </label>
			<div class="col-md-8" style="position: relative;">
				<input type="text" name="date_from" id="from" class="form-control form-control-sm @error('date_from') is-invalid @enderror" value="{{ old('date_from', @$attendanceremark?->date_from) }}" placeholder="Date From">
				@error('date_from')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('date_to') has-error @enderror">
			<label for="to" class="col-form-label col-sm-4">To : </label>
			<div class="col-md-8" style="position: relative;">
				<input type="text" name="date_to" id="to" class="form-control form-control-sm @error('date_to') is-invalid @enderror" value="{{ old('date_to', @$attendanceremark?->date_to) }}" placeholder="Date To">
				@error('date_to')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('attendance_remarks') has-error @enderror">
			<label for="ar" class="col-form-label col-sm-4">Attendance Remarks : </label>
			<div class="col-md-8">
				<textarea name="attendance_remarks" id="ar" class="form-control form-control-sm @error('attendance_remarks') is-invalid @enderror" placeholder="Attendance Remarks (Remark Display For All)">{{ old('attendance_remarks', @$attendanceremark?->attendance_remarks) }}</textarea>
				@error('attendance_remarks')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

	</div>
	<div class="col-sm-6">

		<div class="form-group row m-2 @error('hr_attendance_remarks') has-error @enderror">
			<label for="hrar" class="col-form-label col-sm-4">HR Attendance Remarks : </label>
			<div class="col-md-8">
				<textarea name="hr_attendance_remarks" id="hrar" class="form-control form-control-sm @error('hr_attendance_remarks') is-invalid @enderror" placeholder="HR Attendance Remarks (Remark Display Only For HR, Admin And Director)">{{ old('hr_attendance_remarks', @$attendanceremark?->hr_attendance_remarks) }}</textarea>
				@error('hr_attendance_remarks')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('remarks') has-error @enderror">
			<label for="rem" class="col-form-label col-sm-4">Remarks : </label>
			<div class="col-md-8">
				<textarea name="remarks" id="rem" class="form-control form-control-sm @error('remarks') is-invalid @enderror" placeholder="Remarks (Remark Database : Can Just Leave It Blank)">{{ old('remarks', @$attendanceremark?->remarks) }}</textarea>
				@error('remarks')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

	</div>
</div>
<div class="d-flex justify-content-center m-3">
	<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
	<a href="{{ route('attendanceremark.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
</div>
