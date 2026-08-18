@extends('layouts.app')

@section('content')

<div class="container row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')
	<h4>Add Staff</h4>
	<form method="POST" action="{{ route('staff.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf

	<div class="col-sm-12 row">
		<div class="col-sm-6">

			<div class="form-group row m-2 {{ $errors->has('name') ? 'has-error' : '' }}">
				<label for="nam" class="col-form-label col-sm-4">Name : </label>
				<div class="col-sm-7">
					<input type="text" name="name" value="{{ old('name') }}" id="nam" class="form-control form-control-sm col-sm-12 @error('name') is-invalid @enderror" placeholder="Name">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('ic') ? 'has-error' : '' }}">
				<label for="ic" class="col-form-label col-sm-4">Identity Card/Passport : </label>
				<div class="col-sm-7">
					<input type="text" name="ic" value="{{ old('ic') }}" id="ic" class="form-control form-control-sm col-sm-12 @error('ic') is-invalid @enderror" placeholder="Identity Card/Passport">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('email') ? 'has-error' : '' }}">
				<label for="ema" class="col-form-label col-sm-4">Email : </label>
				<div class="col-sm-7">
					<input type="email" name="email" value="{{ old('email') }}" id="email" class="form-control form-control-sm col-sm-12 @error('email') is-invalid @enderror" placeholder="Email">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('gender_id') ? 'has-error' : '' }}">
				<label for="gender" class="col-form-label col-sm-4">Gender : </label>
				<div class="col-sm-7">
					<?php $i=0 ?>
					@foreach($genders as $g)
					<div class="form-check form-check-inline">
						<label for="gen_{{ $i }}" class="col-form-label">
							<input type="radio" name="gender_id" value="{{ $g->id }}" id="gen_{{ $i }}" class="form-check-input @error('gender_id') is-invalid @enderror" {{ (old('gender_id') == $g->id)?'checked':NULL }}>
						{{$g->gender}}</label>
					</div>
					<?php $i++ ?>
					@endforeach
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('marital_status_id') ? 'has-error' : '' }}">
				<label for="mar" class="col-form-label col-sm-4">Marital Status : </label>
				<div class="col-sm-7">
					<select name="marital_status_id" id="mar" class="form-select form-select-sm col-sm-12 @error('marital_status_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($maritalStatuses as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('marital_status_id') == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('dob') ? 'has-error' : '' }}" style="position: relative">
				<label for="dob" class="col-form-label col-sm-4">Date Of Birth : </label>
				<div class="col-sm-7">
					<input type="text" name="dob" value="{{ old('dob') }}" id="dob" class="form-control form-control-sm col-sm-12 @error('dob') is-invalid @enderror" placeholder="Date Of Birth">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('address') ? 'has-error' : '' }}">
				<label for="add" class="col-form-label col-sm-4">Address : </label>
				<div class="col-sm-7">
					<textarea name="address" id="add" class="form-control form-control-sm col-sm-12 @error('address') is-invalid @enderror" placeholder="Address">{{ old('address') }}</textarea>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('mobile') ? 'has-error' : '' }}">
				<label for="mob" class="col-form-label col-sm-4">Mobile : </label>
				<div class="col-sm-7">
					<input type="text" name="mobile" value="{{ old('mobile') }}" id="mob" class="form-control form-control-sm col-sm-12 @error('mobile') is-invalid @enderror" placeholder="Mobile">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('phone') ? 'has-error' : '' }}">
				<label for="pho" class="col-form-label col-sm-4">Phone : </label>
				<div class="col-sm-7">
					<input type="text" name="phone" value="{{ old('phone') }}" id="mob" class="form-control form-control-sm col-sm-12 @error('phone') is-invalid @enderror" placeholder="Phone">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('religion_id') ? 'has-error' : '' }}">
				<label for="rel" class="col-form-label col-sm-4">Religion : </label>
				<div class="col-sm-7">
					<select name="religion_id" id="rel" class="form-select form-select-sm col-sm-12 @error('religion_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($religions as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('religion_id') == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('race_id') ? 'has-error' : '' }}">
				<label for="rac" class="col-form-label col-sm-4">Race : </label>
				<div class="col-sm-7">
					<select name="race_id" id="rac" class="form-select form-select-sm col-sm-12 @error('race_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($races as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('race_id') == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('nationality_id') ? 'has-error' : '' }}">
				<label for="nat" class="col-form-label col-sm-4">Nationality : </label>
				<div class="col-sm-7">
					<select name="nationality_id" id="nat" class="form-select form-select-sm col-sm-12 @error('nationality_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($countries as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('nationality_id') == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('cimb_account') ? 'has-error' : '' }}">
				<label for="cia" class="col-form-label col-sm-4">CIMB Account : </label>
				<div class="col-sm-7">
					<input type="text" name="cimb_account" value="{{ old('cimb_account') }}" id="cia" class="form-control form-control-sm col-sm-12 @error('cimb_account') is-invalid @enderror" placeholder="CIMB Account">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('epf_account') ? 'has-error' : '' }}">
				<label for="epf" class="col-form-label col-sm-4">EPF Account : </label>
				<div class="col-sm-7">
					<input type="text" name="epf_account" value="{{ old('epf_account') }}" id="epf" class="form-control form-control-sm col-sm-12 @error('epf_account') is-invalid @enderror" placeholder="EPF Account">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('income_tax_no') ? 'has-error' : '' }}">
				<label for="itn" class="col-form-label col-sm-4">Income Tax No : </label>
				<div class="col-sm-7">
					<input type="text" name="income_tax_no" value="{{ old('income_tax_no') }}" id="itn" class="form-control form-control-sm col-sm-12 @error('income_tax_no') is-invalid @enderror" placeholder="Income Tax No">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('socso_no') ? 'has-error' : '' }}">
				<label for="son" class="col-form-label col-sm-4">SOCSO No : </label>
				<div class="col-sm-7">
					<input type="text" name="socso_no" value="{{ old('socso_no') }}" id="son" class="form-control form-control-sm col-sm-12 @error('socso_no') is-invalid @enderror" placeholder="SOCSO No">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('weight') ? 'has-error' : '' }}">
				<label for="wei" class="col-form-label col-sm-4">Weight : </label>
				<div class="col-sm-7">
					<input type="text" name="weight" value="{{ old('weight') }}" id="id" class="form-control form-control-sm col-sm-12 @error('weight') is-invalid @enderror" placeholder="Weight">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('height') ? 'has-error' : '' }}">
				<label for="hei" class="col-form-label col-sm-4">Height : </label>
				<div class="col-sm-7">
					<input type="text" name="height" value="{{ old('height') }}" id="hei" class="form-control form-control-sm col-sm-12 @error('height') is-invalid @enderror" placeholder="Height">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('join') ? 'has-error' : '' }}" style="position: relative">
				<label for="jpo" class="col-form-label col-sm-4">Date Join : </label>
				<div class="col-sm-7">
					<input type="text" name="join" value="{{ old('join') }}" id="jpo" class="form-control form-control-sm col-sm-12 @error('join') is-invalid @enderror" placeholder="Date Join">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('confirmed') ? 'has-error' : '' }}" style="position: relative">
				<label for="jpo" class="col-form-label col-sm-4">Date Confirm : </label>
				<div class="col-sm-7">
					<input type="text" name="confirmed" value="{{ old('confirmed') }}" id="jpo" class="form-control form-control-sm col-sm-12 @error('confirmed') is-invalid @enderror" placeholder="Date Confirm">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('image') ? 'has-error' : '' }}">
				<label for="gender" class="col-form-label col-sm-4">Gender : </label>
				<div class="col-sm-7 supportdoc">
					<input type="file" name="image" value="{{ old('image') }}" id="ima" class="form-control form-control-sm col-sm-12 @error('image') is-invalid @enderror" placeholder="Image">
				</div>
			</div>

			<p>&nbsp;</p>

			<div class="col-sm-12">
				<div class="row m-1">
					<div class="col-sm-5">
						<h6>Staff Emergency Contact</h6>
					</div>
					<div class="col-sm-7">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary emergency_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add Emergency Contact
						</button>
					</div>
				</div>
				<div class="row emergency_wrap">
					<div class="row m-1 emergency_row" id="emergency_row_1">
						<div class="col-sm-1">
							<button class="btn btn-sm btn-outline-secondary emergency_remove" data-index="1" type="button">
								<i class="fas fa-trash" aria-hidden="true"></i>
							</button>
						</div>
						<div class="col-sm-11 form-group {{ $errors->has('staffemergency.*.contact_person') ? 'has-error' : '' }}">
							<input type="hidden" name="staffemergency[1][id]" value="">
							<input type="text" name="staffemergency[1][contact_person]" id="ecp_1" class="form-control form-control-sm" placeholder="Name">
						</div>
						<div class="col-sm-1"></div>
						<div class="col-sm-5 form-group {{ $errors->has('staffemergency.*.phone') ? 'has-error' : '' }}">
							<input type="text" name="staffemergency[1][phone]" id="epp_1" class="form-control form-control-sm" placeholder="Phone">
						</div>
						<div class="col-sm-6 form-group {{ $errors->has('staffemergency.*.relationship_id') ? 'has-error' : '' }}">
							<select name="staffemergency[1][relationship_id]" id="ere_1" class="form-select form-select-sm" placeholder="Relationship"></select>
						</div>
						<div class="col-sm-1"></div>
						<div class="col-sm-11 form-group {{ $errors->has('staffemergency.*.address') ? 'has-error' : '' }}">
							<input type="textarea" name="staffemergency[1][address]" id="ead_1" class="form-control form-control-sm" placeholder="Address">
						</div>
					</div>
				</div>
			</div>

			<p>&nbsp;</p>

			<div class="wrap_spouse col-sm-12">
				<div class="row m-1">
					<div class="col-sm-3">
						<h6>Staff Spouse</h6>
					</div>
					<div class="col-sm-7">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary spouse_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add Spouse
						</button>
					</div>
				</div>
				<div class="row spouse_wrap">
					<!-- JAVASCRIPT ADD FORM SPOUSE -->
				</div>
			</div>

			<p>&nbsp;</p>

			<div class="wrap_children">
				<div class="row m-1">
					<div class="col-sm-3">
						<h6>Staff Children</h6>
					</div>
					<div class="col-sm-7">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary children_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add Children
						</button>
					</div>
				</div>
				<div class="row children_wrap">
					<!-- JAVASCRIPT ADD FORM CHILDREN -->
				</div>
			</div>
		</div>

		<div class="col-sm-6 container">

			<div class="row mb-3 form-group {{ $errors->has('authorise_id') ? 'has-error' : '' }}">
				<div class="col-sm-7 form-check">
					<div class="pretty p-icon p-curve p-tada">
						<input type="hidden" name="authorise_id" value="">
						<input type="checkbox" name="authorise_id" class="form-check-input" value="1" id="adminauth">
						<div class="state p-primary-o">
							<i class="icon mdi mdi-check-all"></i>
							<label class="form-check-label" for="adminauth">System Administrator</label>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('status_id') ? 'has-error' : '' }}">
				<label for="sta" class="col-form-label col-sm-4">Staff Status : </label>
				<div class="col-sm-7">
					<select name="status_id" id="sta" class="form-select form-select-sm @error('status_id') is-invalid @enderror" placeholder="Status"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('username') ? 'has-error' : '' }}">
				<label for="unam" class="col-form-label col-sm-4">Username : </label>
				<div class="col-sm-7">
					<input type="text" name="username" value="{{ old('username') }}" id="unam" class="form-control form-control-sm col-sm-12 @error('username') is-invalid @enderror" placeholder="Username">
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('password') ? 'has-error' : '' }}">
				<label for="pas" class="col-form-label col-sm-4">Password : </label>
				<div class="col-sm-7">
					<input type="text" name="password" value="{{ old('password') }}" id="pas" class="form-control form-control-sm col-sm-12 @error('password') is-invalid @enderror" placeholder="Password">
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('category_id') ? 'has-error' : '' }}">
				<label for="cat" class="col-form-label col-sm-4">Category : </label>
				<div class="col-sm-7">
					<select name="category_id" id="cat" class="form-select form-select-sm @error('category_id') is-invalid @enderror" placeholder="Category"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('branch_id') ? 'has-error' : '' }}">
				<label for="bra" class="col-form-label col-sm-4">Branch : </label>
				<div class="col-sm-7">
					<select name="branch_id" id="bra" class="form-select form-select-sm" placeholder="Branch"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('pivot_dept_id') ? 'has-error' : '' }}">
				<label for="dep" class="col-form-label col-sm-4">Department : </label>
				<div class="col-sm-7">
					<select name="pivot_dept_id" id="dep" class="form-select form-select-sm @error('pivot_dept_id') is-invalid @enderror" placeholder="Department"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('div_id') ? 'has-error' : '' }}">
				<label for="him" class="col-form-label col-sm-4">Management : </label>
				<div class="col-sm-7">
					<select name="div_id" id="him" class="form-select form-select-sm @error('div_id') is-invalid @enderror" placeholder="Management"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('restday_group_id') ? 'has-error' : '' }}">
				<label for="rdg" class="col-form-label col-sm-4">Rest Day Group : </label>
				<div class="col-sm-7">
					<select name="restday_group_id" id="rdg" class="form-select form-select-sm @error('restday_group_id') is-invalid @enderror" placeholder="Please Select"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('leave_flow_id') ? 'has-error' : '' }}">
				<label for="flow" class="col-form-label col-sm-4">Leave Flow Approval : </label>

				<div class="col-sm-7">
				@foreach($leaveApprovalFlows as $k)
					<div class="form-check form-check-inline">
						<div class="pretty p-icon p-curve p-tada mb-2">
							<input type="radio" name="leave_flow_id" class="form-check-input" value="{{ $k->id }}" id="auth{{ $k->id }}">
							<div class="state p-primary-o">
								<i class="icon mdi mdi-check"></i>
								<label class="form-check-label" for="auth{{ $k->id }}">{{ $k->description }}</label>
							</div>
						</div>
					</div>
				@endforeach
				</div>
			</div>

			<div class="form-group row mb-3">
				<div class="row m-0 p-0">
					<div class="col-sm-4">
						<h6>Staff Cross Backup</h6>
					</div>
					<div class="col-sm-7">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary crossbackup_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add Cross Backup
						</button>
					</div>
				</div>
				<div class="row m-0 p-0 crossbackup_wrap">
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('annual_leave') ? 'has-error' : '' }}">
				<label for="annu" class="col-form-label col-sm-4">Annual Leave : </label>
				<div class="col-sm-7">
					<input type="text" name="annual_leave" value="{{ old('annual_leave') }}" id="annu" class="form-control form-control-sm col-sm-12 @error('annual_leave') is-invalid @enderror" placeholder="Annual Leave">
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('mc_leave') ? 'has-error' : '' }}">
				<label for="mcl" class="col-form-label col-sm-4">Medical Certificate Leave : </label>
				<div class="col-sm-7">
					<input type="text" name="mc_leave" value="{{ old('mc_leave') }}" id="mcl" class="form-control form-control-sm col-sm-12 @error('mc_leave') is-invalid @enderror" placeholder="Medical Certificate Leave">
				</div>
			</div>

			<div class="form-group row mb-3" id="wrapmaternity">
				<div class="row m-0 p-0" id="append">
					<div class="row m-0 p-0 {{ $errors->has('maternity_leave') ? 'has-error' : '' }}">
						<label for="matl" id="matl" class="col-sm-4 col-form-label">Maternity Leave : </label>
						<div class="col-sm-7">
							<input type="text" name="maternity_leave" value="{{ old('maternity_leave') }}" id="matl" class="form-control form-control-sm col-sm-12 @error('maternity_leave') is-invalid @enderror" placeholder="Maternity Leave">
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

	<div class="d-flex justify-content-center m-3">
		<button type="submit" class="btn btn-sm btn-outline-secondary">Add Staff</button>
	</div>

	</form>
</div>
@endsection

@section('js')
window.data = {
	route: {
		status: '{{ route('status.status') }}',
		category: '{{ route('category.category') }}',
		branch: '{{ route('branch.branch') }}',
		department: '{{ route('department.department') }}',
		division: '{{ route('division.division') }}',
		crossbackup: '{{ route('staffcrossbackup.staffcrossbackup') }}',
		restdaygroup: '{{ route('restdaygroup.restdaygroup') }}',
		gender: '{{ route('gender.gender') }}',
		educationlevel: '{{ route('educationlevel.educationlevel') }}',
		healthstatus: '{{ route('healthstatus.healthstatus') }}',
		taxexemptionpercentage: '{{ route('taxexemptionpercentage.taxexemptionpercentage') }}',
		relationship: '{{ route('relationship.relationship') }}',
		loginuser: '{{ route('loginuser') }}',
		icuser: '{{ route('icuser') }}',
		emailuser: '{{ route('emailuser') }}',
	},
	url: {},
	isEdit: false,
	maternityLeave: '{{ old('maternity_leave') }}',
	errors: @json($errors->toArray()),
};
@endsection
