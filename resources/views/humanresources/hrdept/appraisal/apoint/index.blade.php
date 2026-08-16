@extends('layouts.app')



@section('content')

<div class="page-humanresources-hrdept-appraisal-apoint-index container">
	@include('humanresources.hrdept.navhr')

	<h4>Appraisal Apoint</h4>

	<div class="row">&nbsp;</div>

	<div class="row">
		<div class="col-6">

			<div class="row mb-3">
				<div class="col-2 text-center" style="border-radius: 10px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
					{{ $newest_year->year }}
				</div>
				<div class="col-10">
					<button type="button" class="btn btn-sm btn-outline-secondary distribute">
						OPEN APPRAISAL
					</button>
				</div>
			</div>

			<div class="row">
				<div class="scrollable-div-1">
					@foreach($staffs as $staff)

					<?php $markers = $markersByEvaluatee[$staff->id] ?? collect(); ?>

					<div class="row hover">
						<div class="col-12 d-flex justify-content-between align-items-center">
							<span>{{ $staff->username }} - {{ $staff->name }}</span>

							@if ($staff->appraisal_category_id == NULL)
							<button type="button" data-bs-toggle="modal" data-bs-target="#form{{ $staff->id }}" data-id="{{ $staff->id }}" class="btn btn-sm py-0 btn-outline-secondary form-button">
								-
							</button>
							@else
							<button type="button" data-bs-toggle="modal" data-bs-target="#form{{ $staff->id }}" data-id="{{ $staff->id }}" class="btn btn-sm py-0 btn-outline-success form-button">
								{{ $staff->category }}
							</button>
							@endif

							<!-- POP UP -->
							<div class="modal fade" id="form{{ $staff->id }}" aria-labelledby="formlabel{{ $staff->id }}" aria-hidden="true">
								<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
									<form method="POST" action="{{ route('appraisalapoint.update', $staff->id) }}" accept-charset="UTF-8" id="form_update" autocomplete="off" class="form-appraisal-category" data-id="{{ $staff->id }}" data-toggle="validator" enctype="multipart/form-data">
										@method('PATCH')
										@csrf
									 <div class="modal-content">
										<div class="modal-header">
											<h1 class="modal-title fs-5" id="formlabel{{ $staff->id }}">Appraisal Form : {{ $staff->username }} - {{ $staff->name }}
											</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<div class="modal-body align-items-start justify-content-center">
											<div class="row mb-1">
												<div class="mb-1">
													<select name="appraisal_category_id{{ $staff->id }}" id="appraisal_category_id{{ $staff->id }}" class="form-control select-input form-select form-select-sm">
														<option value="">Please choose</option>
														@foreach($appraisal_category as $id => $name)
														<option value="{{ $id }}" {{ old('appraisal_category_id' . $staff->id) == $id ? 'selected' : '' }}>{{ $name }}</option>
														@endforeach
													</select>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
										</div>
									</div>
									</form>
								</div>
							</div>
							<!-- POP UP -->

						</div>
					</div>

					@foreach($markers as $marker)
					<div class="row hover">
						<div class="col-12 d-flex justify-content-between align-items-center">
							<span>&nbsp;&nbsp;<i class="bi-x-diamond-fill" style="font-size: 12px;"></i>&nbsp;&nbsp;{{ $marker->username }} - {{ $marker->name }}</span>
							<button type="button" class="pivot_delete" data-id="{{ $marker->id }}">
								<i class="bi-x-square-fill text-danger" aria-hidden="true"></i>
							</button>
						</div>
					</div>
					@endforeach

					<div class="mb-3"></div>
					@endforeach
				</div>
			</div>

		</div>

		<div class="col-6">
			<form method="POST" action="{{ route('appraisalapoint.store') }}" accept-charset="UTF-8" id="form_update" autocomplete="off" class="form-appraisal-category" data-toggle="validator" enctype="multipart/form-data">
				@csrf

			<div class="row mb-3">
				<div class="col-2">
					Evaluator
				</div>
				<div class="col-10">
					<select name="evaluator_id" id="evaluator_id" class="form-control select-input form-select form-select-sm">
						<option value="">Please choose</option>
						@foreach($evaluator as $r1 => $r2)
						<option value="{{ $r1 }}">{{ $r2 }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="row">
				<div class="col-2">
					Evaluatee
				</div>
				<div class="col-10">
					<div class="scrollable-div-2">
						@foreach($evaluatees as $evaluatee)
						<div class="form-check mb-1 g-3">
							<input class="form-check-input" name="evaluetee_id[]" type="checkbox" value="{{ $evaluatee->id }}" id="evaluatee_id{{ $evaluatee->id }}">
							<label class="form-check-label" for="evaluatee_id{{ $evaluatee->id }}">[{{ $evaluatee->department }}]<br />{{ $evaluatee->username }} - {{ $evaluatee->name }}</label>
						</div>
						@endforeach
					</div>
				</div>
			</div>

			<div class="d-flex justify-content-center m-3">
				<button type="submit" class="btn btn-sm btn-outline-secondary">SUBMIT</button>
			</div>

			</form>
		</div>
	</div>

</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
		appraisalapoint: '{{ url('appraisalapoint') }}',
		appraisalapointUpdate: '{{ url('appraisalapoint/update') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
