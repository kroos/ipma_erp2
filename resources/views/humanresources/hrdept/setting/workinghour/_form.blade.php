<div class="col-sm-12 row">

	<div class="form-group row {{ ($errors->has('effective_date_start') || $errors->has('effective_date_end')) ? ' has-error' : '' }} mb-3 g-3">
		<label for="effective_date_start" class="col-form-label col-sm-2">Ramadhan Duration : </label>
		<div class="col-sm-5" style="position: relative;">
			<input type="text" name="effective_date_start" value="{{ old('effective_date_start', @$workinghour?->effective_date_start) }}" id="effective_date_start" class="form-control form-control-sm col-sm-12 @error('effective_date_start') is-invalid @enderror" placeholder="Ramadhan Start">
			@error('effective_date_start')
			<div class="invalid-feedback">
				{{ $message }}
			</div>
			@enderror
		</div>
		<div class="col-sm-5" style="position: relative;">
			<input type="text" name="effective_date_end" value="{{ old('effective_date_end', @$workinghour?->effective_date_end) }}" id="effective_date_end" class="form-control form-control-sm col-sm-12 @error('effective_date_end') is-invalid @enderror" placeholder="Ramadhan End">
			@error('effective_date_end')
			<div class="invalid-feedback">
				{{ $message }}
			</div>
			@enderror
		</div>
	</div>

	@if(isset($workinghour))
	<div class="form-group row {{ $errors->has('time') ? 'has-error' : '' }} mb-3 g-3">
		<label for="tsa" class="col-form-label col-sm-2">Time : </label>
		<div class=" col-sm-2" style="position: relative;">
			<input type="text" name="time_start_am" value="{{ old('time_start_am', $workinghour->time_start_am) }}" id="tsa" class="form-control form-control-sm col-sm-12 @error('time_start_am') is-invalid @enderror" placeholder="1st Half Time Start">
			@error('time_start_am')
			<div class="invalid-feedback">
				{{ $message }}
			</div>
			@enderror
		</div>
		<div class="col-sm-2" style="position: relative;">
			<input type="text" name="time_end_am" value="{{ old('time_end_am', $workinghour->time_end_am) }}" id="tea" class="form-control form-control-sm col-sm-12 @error('time_end_am') is-invalid @enderror" placeholder="1st Half Time End">
			@error('time_end_am')
			<div class="invalid-feedback">
				{{ $message }}
			</div>
			@enderror
		</div>
		<div class="col-sm-2" style="position: relative;">
			<input type="text" name="time_start_pm" value="{{ old('time_start_pm', $workinghour->time_start_pm) }}" id="tsp" class="form-control form-control-sm col-sm-12 @error('time_start_pm') is-invalid @enderror" placeholder="2nd Half Time Start">
			@error('time_start_pm')
			<div class="invalid-feedback">
				{{ $message }}
			</div>
			@enderror
		</div>
		<div class="col-sm-2" style="position: relative;">
			<input type="text" name="time_end_pm" value="{{ old('time_end_pm', $workinghour->time_end_pm) }}" id="tep" class="form-control form-control-sm col-sm-12 @error('time_end_pm') is-invalid @enderror" placeholder="2nd Half Time End">
			@error('time_end_pm')
			<div class="invalid-feedback">
				{{ $message }}
			</div>
			@enderror
		</div>
	</div>

	<div class="form-group row {{ $errors->has('remarks') ? 'has-error' : '' }}  mb-3 g-3">
		<label for="remarks" class="col-form-label col-sm-2">Remarks : </label>
		<div class="col-sm-10">
			<textarea name="remarks" id="remarks" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror" placeholder="Remarks">{{ old('remarks', $workinghour->remarks) }}</textarea>
			@error('remarks')
			<div class="invalid-feedback">
				{{ $message }}
			</div>
			@enderror
		</div>
	</div>
	@endif

	<div class="form-group row">
		<div class="col-sm-10 offset-sm-2">
			<button type="submit" class="btn btn-sm btn-outline-secondary">{{ isset($workinghour) ? 'Submit' : 'Generate Next Year Working Hour' }}</button>
			<a href="{{ route('workinghour.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
		</div>
	</div>

</div>
