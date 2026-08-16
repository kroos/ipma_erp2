<div class="form-group row {{ $errors->has('maternity_leave') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alt" class="col-form-label col-sm-3">Maternity Leave : </label>
	<div class=" col-sm-2">
		<input type="text" name="maternity_leave" value="{{ old('maternity_leave', @$maternityleave?->maternity_leave) }}" id="alt" class="form-control form-control-sm col-sm-12 @error('maternity_leave') is-invalid @enderror" placeholder="Maternity Leave Initialize">
		@error('maternity_leave')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('maternity_leave_adjustment') ? 'has-error' : '' }} mb-3 g-3">
	<label for="ala" class="col-form-label col-sm-3">Maternity Leave Adjustment : </label>
	<div class=" col-sm-2">
		<input type="text" name="maternity_leave_adjustment" value="{{ old('maternity_leave_adjustment', @$maternityleave?->maternity_leave_adjustment) }}" id="ala" class="form-control form-control-sm col-sm-12 @error('maternity_leave_adjustment') is-invalid @enderror" placeholder="Maternity Leave Adjustment">
		@error('maternity_leave_adjustment')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('maternity_leave_utilize') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alu" class="col-form-label col-sm-3">Maternity Leave Utilize : </label>
	<div class=" col-sm-2">
		<input type="text" name="maternity_leave_utilize" value="{{ old('maternity_leave_utilize', @$maternityleave?->maternity_leave_utilize) }}" id="alu" class="form-control form-control-sm col-sm-12 @error('maternity_leave_utilize') is-invalid @enderror" placeholder="Maternity Leave Utilize">
		@error('maternity_leave_utilize')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('maternity_leave_balance') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alb" class="col-form-label col-sm-3">Maternity Leave Balance : </label>
	<div class=" col-sm-2">
		<input type="text" name="maternity_leave_balance" value="{{ old('maternity_leave_balance', @$maternityleave?->maternity_leave_balance) }}" id="alb" class="form-control form-control-sm col-sm-12 @error('maternity_leave_balance') is-invalid @enderror" placeholder="Maternity Leave Balance">
		@error('maternity_leave_balance')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('remarks') ? 'has-error' : '' }} mb-3 g-3">
	<label for="rem" class="col-form-label col-sm-3">Remarks : </label>
	<div class=" col-sm-4">
		<textarea name="remarks" id="rem" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror" placeholder="Remarks">{{ old('remarks', @$maternityleave?->remarks) }}</textarea>
		@error('remarks')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row mb-3 g-3">
	<div class="col-sm-10 offset-sm-3">
		<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
		<a href="{{ route('maternityleave.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
	</div>
</div>
