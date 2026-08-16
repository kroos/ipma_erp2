<div class="form-group row {{ $errors->has('mc_leave') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alt" class="col-form-label col-sm-3">Medical Certificate Leave : </label>
	<div class=" col-sm-2">
		<input type="text" name="mc_leave" value="{{ old('mc_leave', @$mcleave?->mc_leave) }}" id="alt" class="form-control form-control-sm col-sm-12 @error('mc_leave') is-invalid @enderror" placeholder="Medical Certificate Leave Initialize">
		@error('mc_leave')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('mc_leave_adjustment') ? 'has-error' : '' }} mb-3 g-3">
	<label for="ala" class="col-form-label col-sm-3">Medical Certificate Leave Adjustment : </label>
	<div class=" col-sm-2">
		<input type="number" name="mc_leave_adjustment" value="{{ old('mc_leave_adjustment', @$mcleave?->mc_leave_adjustment) }}" id="ala" class="form-control form-control-sm col-sm-12 @error('mc_leave_adjustment') is-invalid @enderror" step="0.5" placeholder="Medical Certificate Leave Adjustment">
		@error('mc_leave_adjustment')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('mc_leave_utilize') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alu" class="col-form-label col-sm-3">Medical Certificate Leave Utilize : </label>
	<div class=" col-sm-2">
		<input type="text" name="mc_leave_utilize" value="{{ old('mc_leave_utilize', @$mcleave?->mc_leave_utilize) }}" id="alu" class="form-control form-control-sm col-sm-12 @error('mc_leave_utilize') is-invalid @enderror" placeholder="Medical Certificate Leave Utilize">
		@error('mc_leave_utilize')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('mc_leave_balance') ? 'has-error' : '' }} mb-3 g-3">
	<label for="alb" class="col-form-label col-sm-3">Medical Certificate Leave Balance : </label>
	<div class=" col-sm-2">
		<input type="text" name="mc_leave_balance" value="{{ old('mc_leave_balance', @$mcleave?->mc_leave_balance) }}" id="alb" class="form-control form-control-sm col-sm-12 @error('mc_leave_balance') is-invalid @enderror" placeholder="Medical Certificate Leave Balance">
		@error('mc_leave_balance')
		<div class="invalid-feedback">
			{{ $message }}
		</div>
		@enderror
	</div>
</div>

<div class="form-group row {{ $errors->has('remarks') ? 'has-error' : '' }} mb-3 g-3">
	<label for="rem" class="col-form-label col-sm-3">Remarks : </label>
	<div class=" col-sm-4">
		<textarea name="remarks" id="rem" class="form-control form-control-sm col-sm-12 @error('remarks') is-invalid @enderror" placeholder="Remarks">{{ old('remarks', @$mcleave?->remarks) }}</textarea>
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
		<a href="{{ route('mcleave.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
	</div>
</div>
