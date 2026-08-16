@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-discipline-edit container">
	@include('humanresources.hrdept.navhr')
	<h4>Update Discipline</h4>

  <form method="POST" action="{{ route('discipline.update', $discipline->id) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
  @csrf
  @method('PATCH')
	<div class="row mt-3">
		<div class="col-md-2">
		<label for="name" class="col-form-label">Name : </label>
		</div>
		<div class="col-md-10">
			<input type="text" name="staff_id" value="{{ old('staff_id', $discipline->belongstostaff->name) }}" id="name" class="form-control form-control-sm @error('staff_id') is-invalid @enderror" readonly>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="supervisor_id" class="col-form-label">Supervisor Incharge : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('supervisor_id') ? 'has-error' : '' }}">
			<select name="supervisor_id" id="supervisor_id" class="form-select form-select-sm @error('supervisor_id') is-invalid @enderror">
				<option value="">Please choose</option>
				@foreach($staff as $k => $v)
				<option value="{{ $k }}" {{ ($discipline->supervisor_id == $k)?'selected':NULL }}>{{ $v }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="disciplinary_action_id" class="col-form-label">Disciplinary Action : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('disciplinary_action_id') ? 'has-error' : '' }}">
			<select name="disciplinary_action_id" id="disciplinary_action_id" class="form-select form-select-sm @error('disciplinary_action_id') is-invalid @enderror">
				<option value="">Please choose</option>
				@foreach($disciplinary_action as $k => $v)
				<option value="{{ $k }}" {{ ($discipline->disciplinary_action_id == $k)?'selected':NULL }}>{{ $v }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="violation_id" class="col-form-label">Violation : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('violation_id') ? 'has-error' : '' }}">
			<select name="violation_id" id="violation_id" class="form-select form-select-sm @error('violation_id') is-invalid @enderror">
				<option value="">Please choose</option>
				@foreach($violation as $k => $v)
				<option value="{{ $k }}" {{ ($discipline->violation_id == $k)?'selected':NULL }}>{{ $v }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="infraction_id" class="col-form-label">Infraction Level : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('infraction_id') ? 'has-error' : '' }}">
			<select name="infraction_id" id="infraction_id" class="form-select form-select-sm @error('infraction_id') is-invalid @enderror">
				<option value="">Please choose</option>
				@foreach($infraction as $k => $v)
				<option value="{{ $k }}" {{ ($discipline->infraction_id == $k)?'selected':NULL }}>{{ $v }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="misconduct_date" class="col-form-label">Misconduct Date : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('misconduct_date') ? 'has-error' : '' }}" style="position: relative;">
			<input type="text" name="misconduct_date" value="{{ old('misconduct_date', $discipline->misconduct_date) }}" id="misconduct_date" class="form-control form-control-sm @error('misconduct_date') is-invalid @enderror">
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="action_taken_date" class="col-form-label">Action Taken Date : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('action_taken_date') ? 'has-error' : '' }}" style="position: relative;">
			<input type="text" name="action_taken_date" value="{{ old('action_taken_date', $discipline->action_taken_date) }}" id="action_taken_date" class="form-control form-control-sm @error('action_taken_date') is-invalid @enderror">
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="reason" class="col-form-label">Description of Incident : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
			<textarea name="reason" id="reason" class="form-control w-100 form-control-sm @error('action_taken_date') is-invalid @enderror">{{ old('reason', $discipline->reason) }}</textarea>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="action_to_be_taken" class="col-form-label">Action to be Taken : </label>
		</div>
		<div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
			<textarea name="action_to_be_taken" id="action_to_be_taken" class="form-control w-100 form-control-sm @error('action_taken_date') is-invalid @enderror">{{ old('action_to_be_taken', $discipline->action_to_be_taken) }}</textarea>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-md-2">
		<label for="softcopy" class="col-form-label">Softcopy : </label>
		</div>
		<div class="col-md-10">
			<input type="file" name="softcopy" value="{{ old('softcopy') }}" id="softcopy" class="form-control form-control-sm @error('softcopy') is-invalid @enderror">
		</div>
	</div>

	@if ($discipline->softcopy)
	<input type="hidden" name="old_softcopy" od="old_softcopy" value="{{ $discipline->softcopy }}">
	<div class="row mt-3">
		<div class="col-md-2"></div>
		<div class="col-md-10">
			<a href="{{ asset('storage/disciplinary/' . $discipline->softcopy) }}" target="_blank">
				{{ $discipline->softcopy }}
			</a>
			&nbsp;&nbsp;&nbsp;(Will Be Replace By New Uploaded Softcopy)
		</div>
	</div>
	@endif

	<div class="row mt-3">
		<div class="col-md-12 text-center">
			<button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
		</div>
	</div>

	</form>

	<div class="row mt-3">
		<div class="col-md-12 text-center">
			<a href="{{ url()->previous() }}">
				<button class="btn btn-sm btn-outline-secondary">BACK</button>
			</a>
		</div>
	</div>

</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
	editId: @json(isset($discipline) ? $discipline->id : null),
};
@endsection

@section('nonjquery')

@endsection
