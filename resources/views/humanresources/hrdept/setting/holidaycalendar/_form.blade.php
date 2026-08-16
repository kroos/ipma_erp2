<div class="row mb-3 g-3" style="position: relative">
	<label for="dstart" class="col-form-label col-sm-2">Date Range : </label>
	<div class="form-group col-sm-5 {{ $errors->has('date_start')?'has-error':'' }}">
		<input type="text" name="date_start" value="{{ old('date_start', @$holidaycalendar?->date_start) }}" id="dstart" class="form-control form-control-sm col-sm-12 @error('date_start') is-invalid @enderror" placeholder="Date Start">
		@error('date_start')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
	<div class="form-group col-sm-5 {{ $errors->has('date_end')?'has-error':'' }}">
		<input type="text" name="date_end" value="{{ old('date_end', @$holidaycalendar?->date_end) }}" id="dend" class="form-control form-control-sm col-sm-12 @error('date_end') is-invalid @enderror" placeholder="Date End">
		@error('date_end')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row mb-3 g-3 {{ $errors->has('holiday')?'has-error':'' }}">
	<label for="hol" class="col-form-label col-sm-2">Holiday : </label>
	<div class="col-sm-10">
		<input type="text" name="holiday" value="{{ old('holiday', @$holidaycalendar?->holiday) }}" id="hol" class="form-control form-control-sm col-sm-12 @error('holiday') is-invalid @enderror" placeholder="Holiday">
		@error('holiday')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row mb-3 g-3 {{ $errors->has('remarks')?'has-error':'' }}">
	<label for="rem" class="col-form-label col-sm-2">Remarks : </label>
	<div class="col-sm-10">
		<textarea name="remarks" id="rem" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror" placeholder="Remarks">{{ old('remarks', @$holidaycalendar?->remarks) }}</textarea>
		@error('remarks')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row mb-3 g-3">
	<div class="col-sm-10 offset-sm-2">
		<button type="submit" class="btn btn-sm btn-outline-secondary">{{ isset($holidaycalendar) ? 'Update Holiday' : 'Add Holiday' }}</button>
		<a href="{{ route('holidaycalendar.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
	</div>
</div>
