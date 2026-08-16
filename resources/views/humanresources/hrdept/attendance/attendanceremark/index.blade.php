@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Attendance Remarks &nbsp; <a href="{{ route('attendanceremark.create') }}" class="btn btn-sm btn-outline-secondary"> <span class="mdi mdi-note-sticky"></span>Add Remark </a></h2>
			<table class="table table-sm table-hover m-3" id="attendanceremark" style="font: 12px roboto-flex;">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>From</th>
						<th>To</th>
						<th>Attendance Remarks</th>
						<th>HR Attendance Remarks</th>
						<th>Remarks</th>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
					@foreach($attendanceremark as $remark)
					<tr>
						<td>{{ $remark->id }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $remark->belongstostaff ? $remark->belongstostaff->name : '' }}">
							{{ Str::limit($remark->belongstostaff ? $remark->belongstostaff->name : '', 20, ' >') }}
						</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $remark->date_from ? \Carbon\Carbon::parse($remark->date_from)->format('j M Y') : '' }}">
							{{ $remark->date_from ? \Carbon\Carbon::parse($remark->date_from)->format('j M Y') : '' }}
						</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $remark->date_to ? \Carbon\Carbon::parse($remark->date_to)->format('j M Y') : '' }}">
							{{ $remark->date_to ? \Carbon\Carbon::parse($remark->date_to)->format('j M Y') : '' }}
						</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $remark->attendance_remarks }}">
							{{ Str::limit($remark->attendance_remarks, 20, ' >') }}
						</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $remark->hr_attendance_remarks }}">
							{{ Str::limit($remark->hr_attendance_remarks, 20, ' >') }}
						</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $remark->remarks }}">
							{{ Str::limit($remark->remarks, 20, ' >') }}
						</td>
						<td>
							<div class="btn-group btn-group-sm" role="group">
								<a href="{{ route('attendanceremark.show', $remark->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-eye fa-beat"></i>
								</a>
								<a href="{{ route('attendanceremark.edit', $remark->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-pen-to-square fa-beat"></i>
								</a>
								<button class="btn btn-sm btn-outline-secondary remark-delete" data-id="{{ $remark->id }}">
									<i class="fa-solid fa-trash-can fa-beat" style="color: red;"></i>
								</button>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection

@section('js')
	@include('humanresources.hrdept.attendance.attendanceremark._js')
@endsection