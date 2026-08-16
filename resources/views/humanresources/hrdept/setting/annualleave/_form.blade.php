<div class="form-group row {{ $errors->has('annual_leave') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alt" class="col-form-label col-sm-3">Annual Leave : </label>
	<div class=" col-sm-2">
		<input type="text" name="annual_leave" value="{{ old('annual_leave', @$annualleave?->annual_leave) }}" id="alt" class="form-control form-control-sm col-sm-12 @error('annual_leave') is-invalid @enderror" placeholder="Annual Leave Initialize">
		@error('annual_leave')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('annual_leave_adjustment') ? 'has-error' : '' }} mb-3 g-3">
	<label for="ala" class="col-form-label col-sm-3">Annual Leave Adjustment : </label>
	<div class=" col-sm-2">
		<input type="number" name="annual_leave_adjustment" value="{{ old('annual_leave_adjustment', @$annualleave?->annual_leave_adjustment) }}" id="ala" class="form-control form-control-sm col-sm-12 @error('annual_leave_adjustment') is-invalid @enderror" placeholder="Annual Leave Adjustment" step="0.5">
		@error('annual_leave_adjustment')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('annual_leave_utilize') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alu" class="col-form-label col-sm-3">Annual Leave Utilize : </label>
	<div class=" col-sm-2">
		<input type="text" name="annual_leave_utilize" value="{{ old('annual_leave_utilize', @$annualleave?->annual_leave_utilize) }}" id="alu" class="form-control form-control-sm col-sm-12 @error('annual_leave_utilize') is-invalid @enderror" placeholder="Annual Leave Utilize">
		@error('annual_leave_utilize')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('annual_leave_balance') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alb" class="col-form-label col-sm-3">Annual Leave Balance : </label>
	<div class=" col-sm-2">
		<input type="text" name="annual_leave_balance" value="{{ old('annual_leave_balance', @$annualleave?->annual_leave_balance) }}" id="alb" class="form-control form-control-sm col-sm-12 @error('annual_leave_balance') is-invalid @enderror" placeholder="Annual Leave Balance">
		@error('annual_leave_balance')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('remarks') ? 'has-error' : '' }} mb-3 g-3">
	<label for="rem" class="col-form-label col-sm-3">Remarks : </label>
	<div class=" col-sm-4">
		<textarea name="remarks" id="rem" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror" placeholder="Remarks">{{ old('remarks', @$annualleave?->remarks) }}</textarea>
		@error('remarks')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row  mb-3 g-3">
	<div class="col-sm-10 offset-sm-3">
		<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
		<a href="{{ route('annualleave.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
	</div>
</div>
