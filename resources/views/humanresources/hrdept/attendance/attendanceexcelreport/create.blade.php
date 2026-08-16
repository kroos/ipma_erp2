@extends('layouts.app')

@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Generate Payslip Excel Report</h4>
	<div class="row justify-content-center">
		<div class="col-sm-6">
		  <form method="POST" action="{{ route('excelreport.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
		    @csrf
			<div class="form-group row mb-3 {{ $errors->has('from') ? 'has-error' : '' }}">
				<label for="from1" class="col-sm-4 col-form-label">From : </label>
				<div class="col-sm-8" style="position:relative;">
					<input type="text" name="from" value="{{ old('from') }}" id="from1" class="form-control form-control-sm col-auto @error('from') is-invalid @enderror" placeholder="From">
				</div>
			</div>
			<div class="form-group row mb-3 {{ $errors->has('to') ? 'has-error' : '' }}">
				<label for="to1" class="col-sm-4 col-form-label">To : </label>
				<div class="col-sm-8" style="position:relative;">
					<input type="text" name="to" value="{{ old('to') }}" id="to1" class="form-control form-control-sm col-auto @error('to') is-invalid @enderror" placeholder="To">
				</div>
			</div>
			<div class="col-sm-12 offset-4 mb-6">
				<button type="submit" class="btn btn-sm btn-outline-secondary">Generate Excel</button>
			</div>
			</form>
		</div>
	</div>
@if ($batchId)
	<p>&nbsp</p>
	<div id="processcsv" class="row col-sm-12">
		<div class="progress col-sm-12" role="progressbar" aria-label="CSV Processing" aria-valuenow="{{ $batch->progress() }}" aria-valuemin="0" aria-valuemax="100">
			<div class="col-sm-auto progress-bar csvprogress rounded-5" style="width: 0%">0% Processing</div>
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
		create: '{{ route('excelreport.create') }}',
	},
	url: {
	},
	old: {
		from: @json(old('from')),
		to: @json(old('to')),
	},
	errors: @json($errors->toArray()),
	@if ($batchId)
	batch: '{{ $batchId }}',
	@endif
};
@endsection
