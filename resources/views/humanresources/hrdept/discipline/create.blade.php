@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-discipline-create container">
	@include('humanresources.hrdept.navhr')
	<h4>Add Discipline</h4>

	<form method="POST" action="{{ route('discipline.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="staff" class="form-label">Name : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
				<select name="staff_id" id="staff" class="form-select form-select-sm col-auto @error('staff_id') is-invalid @enderror">
					<option value="">Please choose</option>
					@foreach($staff as $k => $v)
					<option value="{{ $k }}">{{ $v }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="supervisor" class="form-label">Supervisor Incharge : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('supervisor_id') ? 'has-error' : '' }}">
				<select name="supervisor_id" id="supervisor" class="form-select form-select-sm col-auto @error('supervisor_id') is-invalid @enderror">
					<option value="">Please choose</option>
					@foreach($staff as $k => $v)
					<option value="{{ $k }}">{{ $v }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="disciplinary_action" class="form-label">Disciplinary Action : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('disciplinary_action_id') ? 'has-error' : '' }}">
				<select name="disciplinary_action_id" id="disciplinary_action" class="form-select form-select-sm col-auto @error('disciplinary_action_id') is-invalid @enderror">
					<option value="">Please choose</option>
					@foreach($disciplinary_action as $k => $v)
					<option value="{{ $k }}">{{ $v }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="violation_id" class="form-label">Violation : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('violation_id') ? 'has-error' : '' }}">
				<select name="violation_id" id="violation_id" class="form-select form-select-sm col-auto @error('violation_id') is-invalid @enderror">
					<option value="">Please choose</option>
					@foreach($violation as $k => $v)
					<option value="{{ $k }}">{{ $v }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="infraction_id" class="form-label">Infraction Level : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('infraction_id') ? 'has-error' : '' }}">
				<select name="infraction_id" id="infraction_id" class="form-select form-select-sm col-auto @error('infraction_id') is-invalid @enderror">
					<option value="">Please choose</option>
					@foreach($infraction as $k => $v)
					<option value="{{ $k }}">{{ $v }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="misconduct_date" class="form-label">Misconduct Date : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('misconduct_date') ? 'has-error' : '' }}" style="position: relative;">
				<input type="text" name="misconduct_date" value="{{ old('misconduct_date') }}" id="misconduct_date" class="form-control form-control-sm col-auto @error('misconduct_date') is-invalid @enderror">
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="action_taken_date" class="form-label">Action Taken Date : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('action_taken_date') ? 'has-error' : '' }}" style="position: relative;">
				<input type="text" name="action_taken_date" value="{{ old('action_taken_date') }}" id="action_taken_date" class="form-control form-control-sm col-auto @error('action_taken_date') is-invalid @enderror">
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="reason" class="form-label">Description of Incident : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
				<textarea name="reason" id="reason" class="form-control form-control-sm w-100 col-auto @error('reason') is-invalid @enderror" placeholder="Please Fill-up">{{ old('reason') }}</textarea>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="action_to_be_taken" class="form-label">Action to be Taken : </label>
			</div>
			<div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
				<textarea name="action_to_be_taken" id="action_to_be_taken" class="form-control form-control-sm w-100 col-auto @error('action_to_be_taken') is-invalid @enderror" placeholder="Please Fill-up">{{ old('action_to_be_taken') }}</textarea>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-2">
				<label for="softcopy" class="form-label">Softcopy : </label>
			</div>
			<div class="col-md-10">
				<input type="file" name="softcopy" value="{{ old('softcopy') }}" id="softcopy" class="form-control form-control-sm  col-auto @error('softcopy') is-invalid @enderror">
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-md-12 text-center">
				<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
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
