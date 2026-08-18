@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Attendance Upload</h4>

	<form method="POST" action="{{ route('attendanceupload.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
		@csrf
		<div class="form-group row m-2 {{ $errors->has('softcopy') ? 'has-error' : '' }}">
			<label for="softcopy" class="col-sm-2 col-form-label">Excel File : </label>
			<div class="col-md-10">
				<input type="file" name="softcopy" value="{{ old('softcopy') }}" id="softcopy" class="form-control form-control-sm @error('softcopy') is-invalid @enderror" aria-describedby="progressbar1">
			</div>
		</div>
		<div id="progressbar1" class="form-text text-center">Upload File Progress</div>
		<div id="progressBar" class="progress" role="progressbar" aria-label="Progress Bar with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
			<div class="progress-bar percent_upload" id="percent" style="width: 0%">0% Uploading file/s</div>
		</div>
		<div id="uploadStatus" class="col-sm-12 d-flex justify-content-center"></div>
		<div class="row mt-3">
			<div class="col-md-12 text-center">
				<input type="submit" name="upload" value="Upload" class="btn btn-sm btn-outline-secondary">
			</div>
		</div>
	</form>
	<p>&nbsp;</p>
	@if ($batchId)
	<div id="processcsv" class="row col-sm-12">
		<div class="progress col-sm-12" role="progressbar" aria-label="CSV Processing" aria-valuenow="{{ $batch?->progress() }}" aria-valuemin="0" aria-valuemax="100">
			<div class="col-sm-auto progress-bar csvprogress" style="width: 0%">0% Processing...</div>
		</div>
	</div>
	<div id="uploadStatus" class="col-sm-12 text-center">
		<span id="processedJobs">{{ $batch->processedJobs() }}</span> completed out of {{ $batch->totalJobs }} process
	</div>
	@endif
</div>
@endsection

@section('js')
window.data = {
	route: {
		progress: '{{ route('progress') }}',
		create: '{{ route('attendanceupload.create') }}',
		store: '{{ route('attendanceupload.store') }}',
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
