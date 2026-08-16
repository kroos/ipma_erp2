<div class="col-sm-12 row">
	<div class="col-sm-6">

		<div class="form-group row m-2 @error('customer') has-error @enderror">
			<label for="customer" class="col-form-label col-sm-4">Customer : </label>
			<div class="col-sm-8">
				<input type="text" name="customer" value="{{ old('customer', @$customer?->customer) }}" id="customer" class="form-control form-control-sm col-sm-12 @error('customer') is-invalid @enderror" placeholder="Customer">
				@error('customer')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('contact') has-error @enderror">
			<label for="contact" class="col-form-label col-sm-4">Contact : </label>
			<div class="col-sm-8">
				<input type="text" name="contact" value="{{ old('contact', @$customer?->contact) }}" id="contact" class="form-control form-control-sm col-sm-12 @error('contact') is-invalid @enderror" placeholder="Contact">
				@error('contact')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('phone') has-error @enderror">
			<label for="phone" class="col-form-label col-sm-4">Phone : </label>
			<div class="col-sm-8">
				<input type="text" name="phone" value="{{ old('phone', @$customer?->phone) }}" id="phone" class="form-control form-control-sm col-sm-12 @error('phone') is-invalid @enderror" placeholder="Phone">
				@error('phone')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

	</div>
	<div class="col-sm-6">

		<div class="form-group row m-2 @error('fax') has-error @enderror">
			<label for="fax" class="col-form-label col-sm-4">Fax : </label>
			<div class="col-sm-8">
				<input type="text" name="fax" value="{{ old('fax', @$customer?->fax) }}" id="fax" class="form-control form-control-sm col-sm-12 @error('fax') is-invalid @enderror" placeholder="Fax">
				@error('fax')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('area') has-error @enderror">
			<label for="area" class="col-form-label col-sm-4">Area : </label>
			<div class="col-sm-8">
				<input type="text" name="area" value="{{ old('area', @$customer?->area) }}" id="area" class="form-control form-control-sm col-sm-12 @error('area') is-invalid @enderror" placeholder="Area">
				@error('area')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row m-2 @error('address') has-error @enderror">
			<label for="address" class="col-form-label col-sm-4">Address : </label>
			<div class="col-sm-8">
				<textarea name="address" id="address" class="form-control form-control-sm col-sm-12 @error('address') is-invalid @enderror" placeholder="Address">{{ old('address', @$customer?->address) }}</textarea>
				@error('address')
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
	<a href="{{ route('salescustomer.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
</div>
