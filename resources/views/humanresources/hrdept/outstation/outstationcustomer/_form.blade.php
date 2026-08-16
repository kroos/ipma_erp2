<div class="col-sm-12 row">
	<div class="col-sm-6">

		<div class="form-group row mb-3 @error('customer') has-error @enderror">
			<label for="customer" class="col-form-label col-sm-2">Company Name : </label>
			<div class="col-md-10">
				<input type="text" name="customer" value="{{ old('customer', @$outstationcustomer?->customer) }}" id="customer" class="form-control form-control-sm col-sm-12 @error('customer') is-invalid @enderror" placeholder="Company Name">
				@error('customer')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row mb-3 @error('contact') has-error @enderror">
			<label for="contact" class="col-form-label col-sm-2">Customer Name : </label>
			<div class="col-md-10">
				<input type="text" name="contact" value="{{ old('contact', @$outstationcustomer?->contact) }}" id="contact" class="form-control form-control-sm col-sm-12 @error('contact') is-invalid @enderror" placeholder="Customer Name">
				@error('contact')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row mb-3 @error('phone') has-error @enderror">
			<label for="phone" class="col-form-label col-sm-2">Phone Num : </label>
			<div class="col-md-10">
				<input type="text" name="phone" value="{{ old('phone', @$outstationcustomer?->phone) }}" id="phone" class="form-control form-control-sm col-sm-12 @error('phone') is-invalid @enderror" placeholder="Phone Num">
				@error('phone')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row mb-3 @error('fax') has-error @enderror">
			<label for="fax" class="col-form-label col-sm-2">Fax Num : </label>
			<div class="col-md-10">
				<input type="text" name="fax" value="{{ old('fax', @$outstationcustomer?->fax) }}" id="fax" class="form-control form-control-sm col-sm-12 @error('fax') is-invalid @enderror" placeholder="Fax Num">
				@error('fax')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row mb-3 @error('address') has-error @enderror">
			<label for="address" class="col-form-label col-sm-2">Address : </label>
			<div class="col-md-10">
				<textarea name="address" id="address" class="form-control form-control-sm col-sm-12 @error('address') is-invalid @enderror">{{ old('address', @$outstationcustomer?->address) }}</textarea>
				@error('address')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

	</div>
	<div class="col-sm-6">

		<div class="form-group row mb-3 @error('latitude') has-error @enderror">
			<label for="latitude" class="col-form-label col-sm-2">Latitude : </label>
			<div class="col-md-10">
				<input type="text" name="latitude" value="{{ old('latitude', @$outstationcustomer?->latitude) }}" id="latitude" class="form-control form-control-sm col-sm-12 @error('latitude') is-invalid @enderror" placeholder="Latitude">
				@error('latitude')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

		<div class="form-group row mb-3 @error('longitude') has-error @enderror">
			<label for="longitude" class="col-form-label col-sm-2">Longitude : </label>
			<div class="col-md-10">
				<input type="text" name="longitude" value="{{ old('longitude', @$outstationcustomer?->longitude) }}" id="longitude" class="form-control form-control-sm col-sm-12 @error('longitude') is-invalid @enderror" placeholder="Longitude">
				@error('longitude')
				<div class="invalid-feedback">
					{{ $message }}
				</div>
				@enderror
			</div>
		</div>

	</div>
</div>
<div class="form-group row mb-3 g-3 p-2">
	<div class="col-sm-10 offset-sm-2">
		<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
		<a href="{{ route('outstationcustomer.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
	</div>
</div>
