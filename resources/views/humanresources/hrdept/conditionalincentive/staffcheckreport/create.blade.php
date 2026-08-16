@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h2>Report of Staff Checking Incentive</h2>

	<div class="hstack align-items-start justify-content-between">
		<div class="col-sm-12">
  	   <form method="POST" action="{{ route('cicategorystaffcheckreport.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
      @csrf

			<div class="form-group hstack @error('date_from') has-error is-invalid @enderror">
				<label for="week1" class="col-sm-2 col-form-label">From Week : </label>
				<div class="col-sm-4 align-items-center">
					<select name="date_from" id="week1" class="form-select form-select-sm @error('date_from') is-invalid @enderror"></select>
					@error('date_from') <div id="week1a" class="invalid-feedback">{{ $message }}</div> @enderror
				</div>
			</div>

			<div class="form-group hstack @error('date_to') has-error is-invalid @enderror">
				<label for="week2" class="col-sm-2 col-form-label">To Week : </label>
				<div class="col-sm-4 align-items-center">
					<select name="date_to" id="week2" class="form-select form-select-sm @error('date_to') is-invalid @enderror"></select>
					@error('date_to') <div id="week2a" class="invalid-feedback">{{ $message }}</div> @enderror
				</div>
			</div>

			<div class="offset-sm-2 col-sm-auto mt-3 ">
				<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
			</div>

			</form>
		</div>
	</div>

@if ($batchId)
	<p>&nbsp</p>
	<div id="processcsv" class="row col-sm-12">
		<div class="progress col-sm-12" role="progressbar" aria-label="CSV Processing" aria-valuenow="{{ $batch->progress() }}" aria-valuemin="0" aria-valuemax="100">
			<div class="col-sm-auto progress-bar csvprogress" style="width: 0%">0% CSV Processing</div>
		</div>
	</div>
	<div id="uploadStatus" class="col-sm-auto ">
		<span id="processedJobs">{{ $batch->processedJobs() }}</span> completed out of {{ $batch->totalJobs }} process
	</div>
@endif
</div>
@endsection

@section('js')
window.data = {
	route: {
		progress: '{{ route('progress') }}',
		create: '{{ route('cicategorystaffcheckreport.create') }}',
		weekdates: '{{ route('week_dates') }}',
	},
	url: {
	},
	old: {
	},
	errors: @json($errors->toArray()),
	@if ($batchId)
	batch: '{{ $batchId }}',
	@endif
};
@endsection
