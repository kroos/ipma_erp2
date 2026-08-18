@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')
	<h4>Leaves</h4>
	<p>&nbsp;</p>
	<h5>Cancel Leaves</h5>
	@if($cancel)
	<div class="col-sm-12 table-responsive">
		<table id="upleave" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Leave ID</th>
					<th>Type</th>
					<th>Date Applied</th>
					<th>From</th>
					<th>To</th>
					<th>Duration</th>
					<th>Reason</th>
					<th>Status</th>
					<th>Supp Doc</th>
					<th>Remarks</th>
					<th>Remarks HR</th>
				</tr>
			</thead>
			<tbody>
				@foreach($cancel as $ul)
						<tr>
							<td><a href="{{ route('staff.show', $ul->staff_id) }}" target="_blank">{{ $ul->username }}</a></td>
							<td>{{ $ul->belongstostaff?->name }}</td>
							<td><a href="{{ route('hrleave.show', $ul->id) }}" target="_blank">{{ $ul->leave_ref }}</a></td>
							<td>{{ $ul->belongstooptleavetype?->leave_type_code }}</td>
							<td>{{ $ul->applied_fmt }}</td>
							<td>{{ $ul->dts }}</td>
							<td>{{ $ul->dte }}</td>
							<td>{{ $ul->dper }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $ul->reason }}">{{ Str::limit($ul->reason, 10, ' >') }}</td>
							<td>
								@if(is_null($ul->leave_status_id))
									Pending
								@else
									{{ $ul->belongstooptleavestatus?->status }}
								@endif
							</td>
							<td>
								@if($ul->softcopy)
									<a href="{{ asset('storage/leaves/'.$ul->softcopy) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-richtext"></i></a>
								@else
									<!-- Button trigger modal -->
									<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploaddoc_{{ $ul->id }}">
										<i class="fa-solid fa-upload"></i>
									</button>

									<!-- Modal -->
									<div class="modal fade" id="uploaddoc_{{ $ul->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploaddocLabel_{{ $ul->id }}" aria-hidden="true">
										<div class="modal-dialog">
											<div class="modal-content">
												<form method="POST" action="{{ route('uploaddoc', $ul->id) }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
													@csrf
													@method('PATCH')
												<div class="modal-header">
													<h1 class="modal-title fs-5" id="uploaddocLabel_{{ $ul->id }}">Upload Supporting Document</h1>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body text-center">

													<div class="form-group row m-2 {{ $errors->has('document') ? 'has-error' : '' }}">
														<label for="doc" class="col-form-label col-sm-4">Upload Supporting Document : </label>
														<div class="col-sm-8">
															<input type="file" name="document" value="{{ old('document', @$ul->document) }}" id="doc" class="form-control form-control-sm col-sm-12 @error('document') is-invalid @enderror" placeholder="Supporting Document">
														</div>
													</div>

													<div class="form-group row m-2 {{ $errors->has('amend_note') ? 'has-error' : '' }}">
														<label for="rem" class="col-form-label col-sm-4">Remarks : </label>
														<div class="col-sm-8">
															<textarea name="amend_note" id="rem" class="form-control form-control-sm col-sm-12 @error('amend_note') is-invalid @enderror" placeholder="Remarks">{{ old('amend_note', @$ul->amend_note) }}</textarea>
														</div>
													</div>

												</div>
												<div class="modal-footer">
														<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
														<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
												</div>
												</form>
											</div>
										</div>
									</div>
								@endif
							</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->remarks)??' ' }}">{{ Str::limit($ul->remarks, 10, ' >') }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->hasmanyleaveamend?->first()?->amend_note)??' ' }}">{{ Str::limit($ul->hasmanyleaveamend?->first()?->amend_note, 10, ' >') }}</td>
						</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@else
	<p>No Rejected Leave</p>
	@endif
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
};
@endsection
