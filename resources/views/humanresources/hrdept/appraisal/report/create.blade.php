@extends('layouts.app')
@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<div class="col-sm-12 row">
    <form method="POST" action="{{ route('appraisalexcelreport.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
      @csrf
			<div class="form-group row m-2 {{ $errors->has('year') ? 'has-error' : '' }}">
				<label for="year" class="col-sm-4 col-form-label">Appraisal Report Year :</label>
				<div class="col-sm-8">
					<input name="year" id="year" type="text" class="form-control form-control-sm col-sm-8" placeholder="Year" />
				</div>
			</div>

			<div class="form-group row m-3">
				<div class="col-sm-8 offset-sm-4">
					<button type="submit" class="btn btn-sm btn-outline-secondary">Appraisal Report</button>
				</div>
			</div>
		</form>
	</div>

@if ($batchId)
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
		create: '{{ route('appraisalexcelreport.create') }}',
		store: '{{ route('appraisalexcelreport.store') }}',
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
