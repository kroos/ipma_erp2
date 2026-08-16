@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')
	<h4>Leaves</h4>
	<p>&nbsp;</p>
	<h5>Reject Leaves</h5>
	@if($reject)
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
					<th>Others Approval</th>
					<th>Leave Remarks</th>
					<th>Leave Remarks HR</th>
				</tr>
			</thead>
			<tbody>
				@foreach($reject as $ul)
<?php
if ( ($ul->leave_type_id == 9) || ($ul->leave_type_id != 9 && $ul->half_type_id == 2) || ($ul->leave_type_id != 9 && $ul->half_type_id == 1) ) {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y g:i a');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y g:i a');

	if ($ul->leave_type_id != 9) {
		if ($ul->half_type_id == 2) {
			$dper = $ul->period_day.' Day';
		} elseif($ul->half_type_id == 1) {
			$dper = $ul->period_day.' Day';
		}
	}elseif ($ul->leave_type_id == 9) {
		$i = \Carbon\Carbon::parse($ul->period_time);
		$dper = $i->hour.' hour, '.$i->minute.' minutes';
	}

} else {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y ');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y ');
	$dper = $ul->period_day.' day/s';
}
?>
						<tr>
							<td><a href="{{ route('staff.show', $ul->staff_id) }}" target="_blank">{{ $ul->username }}</a></td>
							<td @if($ul->staff_id) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->belongstostaff?->name }}" @endif>
								{{ Str::words($ul->belongstostaff?->name, 3, ' >') }}
							</td>
							<td><a href="{{ route('hrleave.show', $ul->id) }}" target="_blank">HR9-{{ str_pad( $ul->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $ul->leave_year }}</a></td>
							<td>{{ $ul->belongstooptleavetype?->leave_type_code }}</td>
							<td>{{ Carbon::parse($ul->created_at)->format('j M Y') }}</td>
							<td>{{ $dts }}</td>
							<td>{{ $dte }}</td>
							<td>{{ $dper }}</td>
							<td @if($ul->reason) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->reason }}" @endif>
								{{ Str::limit($ul->reason, 10, ' >') }}
							</td>
							<td>
								@if(is_null($ul->leave_status_id))
									Pending
								@else
									{{ $ul->belongstooptleavestatus?->status }}
								@endif
							</td>
							<td>


								<table class="table table-hover table-sm">
									<tbody>
										@if($ul->hasmanyleaveapprovalbackup?->isNotEmpty())
											<tr>
												<!-- <td>Backup {{ $ul->hasmanyleaveapprovalbackup?->first()->belongstostaff?->name }}</td> -->
												<th>Backup</th>
												<td>{{ $ul->hasmanyleaveapprovalbackup?->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td @if($ul->hasmanyleaveapprovalbackup?->first()?->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->hasmanyleaveapprovalbackup?->first()?->remarks }}" @endif>{{ Str::limit($ul->hasmanyleaveapprovalbackup?->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovalsupervisor?->isNotEmpty())
											<tr>
												<!-- <td>Supervisor {{ $ul->hasmanyleaveapprovalsupervisor?->first()->belongstostaff?->name }}</td> -->
												<th>Supervisor</th>
												<td>{{ $ul->hasmanyleaveapprovalsupervisor?->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td @if($ul->hasmanyleaveapprovalsupervisor?->first()?->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->hasmanyleaveapprovalsupervisor?->first()?->remarks }}" @endif>{{ Str::limit($ul->hasmanyleaveapprovalsupervisor?->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovalhod?->isNotEmpty())
											<tr>
												<!-- <td>HOD {{ $ul->hasmanyleaveapprovalhod?->first()->belongstostaff?->name }}</td> -->
												<th>HOD</th>
												<td>{{ $ul->hasmanyleaveapprovalhod?->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td @if($ul->hasmanyleaveapprovalhod?->first()?->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->hasmanyleaveapprovalhod?->first()?->remarks }}" @endif>{{ Str::limit($ul->hasmanyleaveapprovalhod?->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovaldir?->isNotEmpty())
											<tr>
												<!-- <td>Director {{ $ul->hasmanyleaveapprovaldir?->first()->belongstostaff?->name }}</td> -->
												<th>Director</th>
												<td>{{ $ul->hasmanyleaveapprovaldir?->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td @if($ul->hasmanyleaveapprovaldir?->first()?->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->hasmanyleaveapprovaldir?->first()?->remarks }}" @endif>{{ Str::limit($ul->hasmanyleaveapprovaldir?->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovalhr?->isNotEmpty())
											<tr>
												<!-- <td>HR {{ $ul->hasmanyleaveapprovalhr?->first()->belongstostaff?->name }}</td> -->
												<th>HR</th>
												<td>{{ $ul->hasmanyleaveapprovalhr?->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td @if($ul->hasmanyleaveapprovalhr?->first()?->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->hasmanyleaveapprovalhr?->first()?->remarks }}" @endif>{{ Str::limit($ul->hasmanyleaveapprovalhr?->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif
									</tbody>
								</table>

								@if($ul->softcopy)
									<!-- <a href="{{ asset('storage/leaves/'.$ul->softcopy) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-richtext"></i></a> -->
								@else
									<!-- Button trigger modal -->
									<!-- <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploaddoc_{{ $ul->id }}">
										<i class="fa-solid fa-upload"></i>
									</button> -->

									<!-- Modal -->
									<!-- <div class="modal fade" id="uploaddoc_{{ $ul->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploaddocLabel_{{ $ul->id }}" aria-hidden="true">
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
															<input type="file" name="document" value="{{ old('document') }}" id="doc" class="form-control form-control-sm col-sm-12 @error('document') is-invalid @enderror" placeholder="Supporting Document">
														</div>
													</div>

													<div class="form-group row m-2 {{ $errors->has('amend_note') ? 'has-error' : '' }}">
														<label for="id" class="col-form-label col-sm-4">Label : </label>
														<div class="col-sm-8">
															<textarea name="amend_note" id="rem" class="form-control form-control-sm col-sm-12 @error('amend_note') is-invalid @enderror" placeholder="Remarks">{{ old('amend_note') }}</textarea>
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
									</div> -->
								@endif
							</td>
							<td @if($ul->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->remarks }}" @endif>
								{{ Str::limit($ul->remarks, 10, ' >') }}
							</td>
							<td @if($ul->hasmanyleaveamend?->first()?->amend_note) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $ul->hasmanyleaveamend?->first()?->amend_note }}" @endif>
								{{ Str::limit($ul->hasmanyleaveamend?->first()?->amend_note, 10, ' >') }}
							</td>
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

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
// fullcalendar cant use jquery
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
