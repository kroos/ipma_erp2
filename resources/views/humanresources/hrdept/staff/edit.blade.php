@extends('layouts.app')

@section('content')

<div class="container row justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Edit Staff</h4>
	<form method="POST" action="{{ route('staff.update', $staff) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
		@method('PATCH')

	<div class="col-sm-12 row">
		<div class="col-sm-6">

			<div class="form-group row m-2 {{ $errors->has('name') ? 'has-error' : '' }}">
				<label for="nam" class="col-form-label col-sm-4">Name : </label>
				<div class="col-sm-7">
					<input type="text" name="name" value="{{ old('name', $staff->name) }}" id="nam" class="form-control form-control-sm col-sm-12 @error('name') is-invalid @enderror" placeholder="Name">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('ic') ? 'has-error' : '' }}">
				<label for="ic" class="col-form-label col-sm-4">Identity Card/Passport : </label>
				<div class="col-sm-7">
					<input type="text" name="ic" value="{{ old('ic', $staff->ic) }}" id="ic" class="form-control form-control-sm col-sm-12 @error('ic') is-invalid @enderror" placeholder="Identity Card/Passport">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('dob') ? 'has-error' : '' }}" style="position: relative">
				<label for="dob" class="col-form-label col-sm-4">Date Of Birth : </label>
				<div class="col-sm-7">
					<input type="text" name="dob" value="{{ old('dob', $staff->dob) }}" id="dob" class="form-control form-control-sm col-sm-12 @error('dob') is-invalid @enderror" placeholder="Date Of Birth">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('gender_id') ? 'has-error' : '' }}">
				<label for="gender" class="col-form-label col-sm-4">Gender : </label>
				<div class="col-sm-7">
					<?php $i=0 ?>
					@foreach($genders as $g)
					<div class="form-check form-check-inline">
						<label for="gen_{{ $i }}" class="col-form-label mx-2">
							<input type="radio" name="gender_id" value="{{ $g->id }}" id="gen_{{ $i }}" class="form-check-input @error('gender_id') is-invalid @enderror" {{ ( old('gender_id', $staff->gender_id) == $g->id )?'checked':NULL }}>
						{{ $g->gender }}</label>
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
							<option value="{{ $k1 }}" {{ (old('marital_status_id', $staff->marital_status_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('email') ? 'has-error' : '' }}">
				<label for="ema" class="col-form-label col-sm-4">Email : </label>
				<div class="col-sm-7">
					<input type="text" name="email" value="{{ old('email', $staff->email) }}" id="id" class="form-control form-control-sm col-sm-12 @error('email') is-invalid @enderror" placeholder="Name">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('address') ? 'has-error' : '' }}">
				<label for="add" class="col-form-label col-sm-4">Address : </label>
				<div class="col-sm-7">
					<textarea name="address" id="add" class="form-control form-control-sm col-sm-12 @error('address') is-invalid @enderror" placeholder="Address">{{ old('address', $staff->address) }}</textarea>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('mobile') ? 'has-error' : '' }}">
				<label for="mob" class="col-form-label col-sm-4">Mobile : </label>
				<div class="col-sm-7">
					<input type="text" name="mobile" value="{{ old('mobile', $staff->mobile) }}" id="mob" class="form-control form-control-sm col-sm-12 @error('mobile') is-invalid @enderror" placeholder="Mobile">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('phone') ? 'has-error' : '' }}">
				<label for="pho" class="col-form-label col-sm-4">Phone : </label>
				<div class="col-sm-7">
					<input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" id="pho" class="form-control form-control-sm col-sm-12 @error('phone') is-invalid @enderror" placeholder="Phone">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('religion_id') ? 'has-error' : '' }}">
				<label for="rel" class="col-form-label col-sm-4">Religion : </label>
				<div class="col-sm-7">
					<select name="religion_id" id="rel" class="form-select form-select-sm col-sm-12 @error('religion_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($religions as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('religion_id', $staff->religion_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
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
							<option value="{{ $k1 }}" {{ (old('race_id', $staff->race_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
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
							<option value="{{ $k1 }}" {{ (old('nationality_id', $staff->nationality_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('cimb_account') ? 'has-error' : '' }}">
				<label for="cia" class="col-form-label col-sm-4">CIMB Account : </label>
				<div class="col-sm-7">
					<input type="text" name="cimb_account" value="{{ old('cimb_account', $staff->cimb_account) }}" id="cia" class="form-control form-control-sm col-sm-12 @error('cimb_account') is-invalid @enderror" placeholder="CIMB Account">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('epf_account') ? 'has-error' : '' }}">
				<label for="epf" class="col-form-label col-sm-4">EPF Account : </label>
				<div class="col-sm-7">
					<input type="text" name="epf_account" value="{{ old('epf_account', $staff->epf_account) }}" id="epf" class="form-control form-control-sm col-sm-12 @error('epf_account') is-invalid @enderror" placeholder="EPF Account">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('income_tax_no') ? 'has-error' : '' }}">
				<label for="itn" class="col-form-label col-sm-4">Income Tax No : </label>
				<div class="col-sm-7">
					<input type="text" name="income_tax_no" value="{{ old('income_tax_no', $staff->income_tax_no) }}" id="itn" class="form-control form-control-sm col-sm-12 @error('income_tax_no') is-invalid @enderror" placeholder="Income Tax No">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('socso_no') ? 'has-error' : '' }}">
				<label for="son" class="col-form-label col-sm-4">SOCSO No : </label>
				<div class="col-sm-7">
					<input type="text" name="socso_no" value="{{ old('socso_no', $staff->socso_no) }}" id="son" class="form-control form-control-sm col-sm-12 @error('socso_no') is-invalid @enderror" placeholder="SOCSO No">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('weight') ? 'has-error' : '' }}">
				<label for="wei" class="col-form-label col-sm-4">Weight : </label>
				<div class="col-sm-7">
					<input type="text" name="weight" value="{{ old('weight', $staff->weight) }}" id="wei" class="form-control form-control-sm col-sm-12 @error('weight') is-invalid @enderror" placeholder="Weight">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('height') ? 'has-error' : '' }}">
				<label for="hei" class="col-form-label col-sm-4">Height : </label>
				<div class="col-sm-7">
					<input type="text" name="height" value="{{ old('height', $staff->height) }}" id="hei" class="form-control form-control-sm col-sm-12 @error('height') is-invalid @enderror" placeholder="Height">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('join') ? 'has-error' : '' }}">
				<label for="jpo" class="col-form-label col-sm-4">Date Join : </label>
				<div class="col-sm-7" style="position: relative">
					<input type="text" name="join" value="{{ old('join', $staff->join) }}" id="jpo" class="form-control form-control-sm col-sm-12 @error('join') is-invalid @enderror" placeholder="Date Join">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('confirmed') ? 'has-error' : '' }}">
				<label for="jpo" class="col-form-label col-sm-4">Date Confirm : </label>
				<div class="col-sm-7" style="position: relative">
					<input type="text" name="confirmed" value="{{ old('confirmed', $staff->confirmed) }}" id="jpo" class="form-control form-control-sm col-sm-12 @error('confirmed') is-invalid @enderror" placeholder="Date Confirm">
				</div>
			</div>

			<div class="form-group row m-2 {{ $errors->has('image') ? 'has-error' : '' }}">
				<label for="ima" class="col-form-label col-sm-4">Image : </label>
				<div class="col-sm-7 supportdoc">
					<input type="file" name="image" value="{{ old('image', $staff->image) }}" id="ima" class="form-control form-control-sm form-control-file @error('image') is-invalid @enderror" placeholder="Image">
				</div>
			</div>

			<p>&nbsp;</p>

			<div class="col-sm-12">
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
					@if($spouses->count())
						<?php $i=1 ?>
						@foreach($spouses as $spouse)
							<div class="row m-1 spouse_row" id="spouse_row_{{ $i }}">
								<div class="col-sm-1">
									<button class="btn btn-sm btn-outline-secondary spouse_remove" data-index="{{ $i }}" type="button">
										<i class="fas fa-trash" aria-hidden="true"></i>
									</button>
								</div>
								<div class="col-sm-11 form-group {{ $errors->has('staffspouse.*.spouse') ? 'has-error' : '' }}">
									<input type="hidden" name="staffspouse[{{ $i }}][id]" value="{{ $spouse->id }}">
									<input type="text" name="staffspouse[{{ $i }}][spouse]" id="spo" value="{{ $spouse->spouse }}" class="form-control form-control-sm" placeholder="Spouse">
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-5 form-group {{ $errors->has('staffspouse.*.phone') ? 'has-error' : '' }}">
									<input type="text" name="staffspouse[{{ $i }}][phone]" value="{{ $spouse->phone }}" id="pho" class="form-control form-control-sm" placeholder="Spouse Phone">
								</div>
								<div class="col-sm-6 form-group {{ $errors->has('staffspouse.*.profession') ? 'has-error' : '' }}">
									<input type="text" name="staffspouse[{{ $i }}][profession]" value="{{ $spouse->profession }}" id="pro" class="form-control form-control-sm" placeholder="Spouse Profession">
								</div>
							</div>
							<?php $i++ ?>
						@endforeach
					@endif
				</div>
			</div>

			<p>&nbsp;</p>

			<div class="col-sm-12">
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
					@if($childrens->count())
						<?php $i=1 ?>
						@foreach($childrens as $child)
							<div class="row m-1 children_row" id="children_row_{{ $i }}">
								<div class="col-sm-1">
									<button class="btn btn-sm btn-outline-secondary children_remove" data-index="{{ $i }}" type="button">
										<i class="fas fa-trash" aria-hidden="true"></i>
									</button>
								</div>
								<div class="col-sm-11 form-group {{ $errors->has('staffchildren.*.children') ? 'has-error' : '' }}">
									<input type="hidden" name="staffchildren[{{ $i }}][id]" value="{{ $child->id }}">
									<input type="text" name="staffchildren[{{ $i }}][children]" value="{{ $child->children }}" id="chi_{{ $i }}" class="form-control form-control-sm" placeholder="Children">
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-7 form-group {{ $errors->has('staffchildren.*.dob') ? 'has-error' : '' }}" style="position: relative">
									<input type="text" name="staffchildren[{{ $i }}][dob]" value="{{ old('staffchildren.' . $i . '.dob', $child->dob) }}" id="cdo_{{ $i }}" class="form-control form-control-sm" placeholder="Date Of Birth">
								</div>
								<div class="col-sm-4 form-group {{ $errors->has('staffchildren.*.gender_id') ? 'has-error' : '' }}">
									<select name="staffchildren[{{ $i }}][gender_id]" id="cge_{{ $i }}" class="form-select form-select-sm" placeholder="Gender">
										<option value="">Gender</option>
									@foreach($genders as $g)
										<option value="{{ $g->id }}" {{ ($g->id == $child->gender_id)?'selected':NULL }}>{{ $g->gender }}</option>
									@endforeach
									</select>
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-7 form-group {{ $errors->has('staffchildren.*.education_level_id') ? 'has-error' : '' }}">
									<select name="staffchildren[{{ $i }}][education_level_id]" id="cel_{{ $i }}" class="form-select form-select-sm" placeholder="Education Level">
										<option value="">Education Level</option>
									@foreach($educationLevels as $el)
										<option value="{{ $el->id }}" {{ ($el->id == $child->education_level_id)?'selected':'' }}>{{ $el->education_level }}</option>
									@endforeach
									</select>
								</div>
								<div class="col-sm-4 form-group {{ $errors->has('staffchildren.*.health_status_id') ? 'has-error' : '' }}">
									<select name="staffchildren[{{ $i }}][health_status_id]" id="chs_{{ $i }}" class="form-select form-select-sm" placeholder="Health Status">
										<option value="">Health Status</option>
									@foreach($healthStatuses as $hs)
										<option value="{{ $hs->id }}" {{ ($hs->id == $child->health_status_id)?'selected':NULL }}>{{ $hs->health_status }}</option>
									@endforeach
									</select>
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-5 form-group form-check {{ $errors->has('staffchildren.*.tax_exemption') ? 'has-error' : '' }}">
									<input type="hidden" name="staffchildren[{{ $i }}][tax_exemption]" class="form-check-input" value="0">
									<input type="checkbox" name="staffchildren[{{ $i }}][tax_exemption]" class="form-check-input" value="1" id="cte_{{ $i }}" {{ ($child->tax_exemption)?'checked':NULL }}>
									<label class="form-check-label" for="cte_{{ $i }}">Valid for Tax Exemption?</label>
								</div>
								<div class="col-sm-6 form-group {{ $errors->has('staffchildren.*.tax_exemption_percentage_id') ? 'has-error' : '' }}">
									<select name="staffchildren[{{ $i }}][tax_exemption_percentage_id]" id="ctep_{{ $i }}" class="form-select form-select-sm" placeholder="Tax Exemption Percentage">
										<option value="">Tax Exemption Percentage</option>
									@foreach($taxExemptionPercentages as $tep)
										<option value="{{ $tep->id }}" {{ ($tep->id == $child->tax_exemption_percentage_id)?'selected':NULL }}>{{ $tep->tax_exemption_percentage }}</option>
									@endforeach
									</select>
								</div>
							</div>
							<?php $i++ ?>
						@endforeach
					@endif
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
					@if($emergencies->count())
						<?php $i=1 ?>
						@foreach($emergencies as $emerg)
							<div class="row m-1 emergency_row" id="emergency_row_{{ $i }}">
								<div class="col-sm-1">
									<button class="btn btn-sm btn-outline-secondary emergency_remove" data-index="{{ $i }}" type="button">
										<i class="fas fa-trash" aria-hidden="true"></i>
									</button>
								</div>
								<div class="col-sm-11 form-group {{ $errors->has('staffemergency.*.contact_person') ? 'has-error' : '' }}">
									<input type="hidden" name="staffemergency[{{ $i }}][id]" value="{{ $emerg->id }}">
									<input type="text" name="staffemergency[{{ $i }}][contact_person]" value="{{ $emerg->contact_person }}" id="ecp_{{ $i }}" class="form-control form-control-sm" placeholder="Emergency Contact">
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-5 form-group {{ $errors->has('staffemergency.*.phone') ? 'has-error' : '' }}">
									<input type="text" name="staffemergency[{{ $i }}][phone]" value="{{ $emerg->phone }}" id="epp_{{ $i }}" class="form-control form-control-sm" placeholder="Phone">
								</div>
								<div class="col-sm-6 form-group {{ $errors->has('staffemergency.*.relationship_id') ? 'has-error' : '' }}">
									<select name="staffemergency[{{ $i }}][relationship_id]" id="ere_{{ $i }}" class="form-select form-select-sm" placeholder="Relationship">
											<option value="">Relationship</option>												@foreach($relationships as $rel)
											<option value="{{ $rel->id }}" {{ ($rel->id == $emerg->relationship_id)?'selected':NULL }}>{{ $rel->relationship }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-11 form-group {{ $errors->has('staffemergency.*.address') ? 'has-error' : '' }}">
									<input type="textarea" name="staffemergency[{{ $i }}][address]" value="{{ $emerg->address }}" id="ead_{{ $i }}" class="form-control form-control-sm" placeholder="Address">
								</div>
							</div>
							<?php $i++ ?>
						@endforeach
					@endif
				</div>
			</div>
		</div>

		<div class="col-sm-6 container">

			<div class="row mb-3 form-group {{ $errors->has('authorise_id') ? 'has-error' : '' }}">
				<div class="col-sm-7 form-check">
					<div class="pretty p-icon p-curve p-tada">
						<input type="hidden" name="authorise_id" value="">
						<input type="checkbox" name="authorise_id" value="1" id="authjj" class="form-check-input @error('authorise_id') is-invalid @enderror" {{ ( old('authorise_id', $staff->authorise_id) == 1 )?'checked':NULL }}>
						<div class="state p-primary-o">
							<i class="icon mdi mdi-check-all"></i>
							<label class="form-check-label" for="authjj">System Administrator</label>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('status_id') ? 'has-error' : '' }}">
				<label for="sta" class="col-form-label col-sm-4">Staff Status : </label>
				<div class="col-sm-7">
					<select name="status_id" id="sta" class="form-select form-select-sm col-sm-12 @error('status_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($statuses as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('status_id', $staff->status_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('username') ? 'has-error' : '' }}">
				<label for="unam" class="col-form-label col-sm-4">Username : </label>
				<div class="col-sm-7">
					<input type="text" name="username" value="{{ old('username', $username) }}" id="unam" class="form-control form-control-sm col-sm-12 @error('username') is-invalid @enderror" placeholder="Username">
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('password') ? 'has-error' : '' }}">
				<label for="pas" class="col-form-label col-sm-4">Password : </label>
				<div class="col-sm-7">
					<input type="password" name="password" value="{{ old('password') }}" id="pas" class="form-control form-control-sm col-sm-12 @error('password') is-invalid @enderror" placeholder="Password">
					<div id="passHelp" class="form-text">Insert password if only need to be change. Otherwise, just leave it.</div>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('category_id') ? 'has-error' : '' }}">
				<label for="cat" class="col-form-label col-sm-4">Category : </label>
				<div class="col-sm-7">
					<select name="category_id" id="cat" class="form-select form-select-sm col-sm-12 @error('category_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($categories as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('category_id', $mainDept?->category_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('branch_id') ? 'has-error' : '' }}">
				<label for="bra" class="col-form-label col-sm-4">Branch : </label>
				<div class="col-sm-7">
					<select name="branch_id" id="bra" class="form-select form-select-sm col-sm-12 @error('branch_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($branches as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('branch_id', $mainDept?->branch_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('pivot_dept_id') ? 'has-error' : '' }}">
				<label for="dep" class="col-form-label col-sm-4">Department : </label>
				<div class="col-sm-7">
					<select name="pivot_dept_id" id="dep" class="form-select form-select-sm col-sm-12 @error('pivot_dept_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($departments as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('pivot_dept_id', $mainDept?->id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('div_id') ? 'has-error' : '' }}">
				<label for="him" class="col-form-label col-sm-4">Management : </label>
				<div class="col-sm-7">
					<select name="div_id" id="him" class="form-select form-select-sm col-sm-12 @error('div_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($divisions as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('div_id', $staff->div_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('restday_group_id') ? 'has-error' : '' }}">
				<label for="rdg" class="col-form-label col-sm-4">Rest Day Group : </label>
				<div class="col-sm-7">
					<select name="restday_group_id" id="rdg" class="form-select form-select-sm col-sm-12 @error('restday_group_id') is-invalid @enderror">
						<option value="">Please choose</option>
						@foreach($restdayGroups as $k1 => $v1)
							<option value="{{ $k1 }}" {{ (old('restday_group_id', $staff->restday_group_id) == $k1)?'selected':NULL }}>{{ $v1 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('leave_flow_id') ? 'has-error' : '' }}">
				<label for="flow" class="col-form-label col-sm-4">Leave Flow Approval : </label>

				<div class="col-sm-7">
				@foreach($leaveApprovalFlows as $k)
					<div class="form-check form-check-inline">
						<div class="pretty p-icon p-curve p-tada mb-2">
							<input type="radio" name="leave_flow_id" class="form-check-input" value="{{ $k->id }}" {{ ($staff->leave_flow_id == $k->id)?'checked':NULL }} id="auth">
							<div class="state p-primary-o">
								<i class="icon mdi mdi-check"></i>
								<label class="form-check-label" for="auth">{{ $k->description }}</label>
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
					@if($crossbackups->count())
						<?php $i=1 ?>
						@foreach($crossbackups as $cb)
							<div class="row m-1 p-0 crossbackup_row" id="crossbackup_row_{{ $i }}">
								<div class="col-sm-1">
									<button type="button" class="btn btn-sm btn-outline-secondary crossbackup_remove" data-index="{{ $i }}">
										<i class="fas fa-trash" aria-hidden="true"></i>
									</button>
								</div>
								<div class="col-sm-10 form-group {{ $errors->has('crossbackup.*.backup_staff_id') ? 'has-error' : '' }}">
									<input type="hidden" name="crossbackup[{{ $i }}][active]" value="1">
									<input type="hidden" name="crossbackup[{{ $i }}][id]" value="{{ $cb->id }}">
									<select name="crossbackup[{{ $i }}][backup_staff_id]" id="sta_{{ $i }}" class="form-select form-select-sm" placeholder="Cross Backup Personnel">
										<option value="">Cross Backup Personnel</option>
										@foreach($activeStaff as $st)
											<option value="{{ $st->id }}" {{ ($st->id == $cb->id)?'selected':NULL }}>{{ $st->name }}</option>
										@endforeach
									</select>
								</div>
							</div>
						<?php $i++ ?>
						@endforeach
					@endif
				</div>
			</div>
		</div>
	</div>

	<div class="d-flex justify-content-center m-3">
		<button type="submit" class="btn btn-sm btn-outline-secondary">Update Staff</button>
	</div>

	</form>
</div>
@endsection

@section('js')
window.data = {
	route: {
		department: '{{ route('department.department') }}',
		crossbackup: '{{ route('staffcrossbackup.staffcrossbackup') }}',
		restdaygroup: '{{ route('restdaygroup.restdaygroup') }}',
		gender: '{{ route('gender.gender') }}',
		educationlevel: '{{ route('educationlevel.educationlevel') }}',
		healthstatus: '{{ route('healthstatus.healthstatus') }}',
		taxexemptionpercentage: '{{ route('taxexemptionpercentage.taxexemptionpercentage') }}',
		relationship: '{{ route('relationship.relationship') }}',
	},
	url: {
		spouse: '{{ url('spouse') }}',
		children: '{{ url('children') }}',
		emergencycontact: '{{ url('emergencycontact') }}',
		deletecrossbackup: '{{ url('api/deletecrossbackup') }}',
	},
	isEdit: true,
	staffId: {{ $staff->id }},
	spouseCount: {{ $spouseCount }},
	childrenCount: {{ $childrenCount }},
	emergencyCount: {{ $emergencyCount }},
	crossbackupCount: {{ $crossbackupCount }},
	maternityLeave: '{{ old('maternity_leave', $staff->maternity_leave) }}',
	errors: @json($errors->toArray()),
};
@endsection
