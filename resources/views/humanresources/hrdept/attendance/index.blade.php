@extends('layouts.app')

@section('content')

<div class="container row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')

	<h4>Attendance</h4>

	<div class="col-sm-12 row m-1">
		  <form method="POST" action="{{ route('attendance.index') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
		    @csrf
			<div class="col-sm-8 row g-3" style="position: relative;">
				<input type="text" name="date" value="{{ old('date', $selected_date) }}" id="date" class="col-sm-2 form-control form-control-sm @error('date') is-invalid @enderror" placeholder="Date">
				<div class="col-auto">
					<button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
				</div>
			</div>
		</form>
	</div>

	<div class="col-sm-12 table-responsive">
		<table id="attendance" class="table table-hover table-sm align-middle caption-top" style="font-size:12px">
			<caption>
				Legend:
				<span class="p-1 m-1 fw-bold" style="background-color: #d5f5e3;">Approve Leave</span>
				<span class="p-1 m-1 fw-bold" style="background-color: #fadbd8;">Pending Leave</span>
			</caption>
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Type</th>
					<th>Cause</th>
					<th>Leave</th>
					<th>Date</th>
					<th>In</th>
					<th>Break</th>
					<th>Resume</th>
					<th>Out</th>
					<th>Duration</th>
					<th>Overtime</th>
					<th>Outstation</th>
					<th>Remarks</th>
					<th>Exception</th>
					<th>Edit</th>
				</tr>
			</thead>
			<tbody>

			@foreach($attendance as $s)
			@php $g = $grid[$s->id] ?? null; @endphp
			@if($g)
				<tr>
					<td>
						<a href="{{ route('attendance.edit', $s->id) }}" target="_blank">{{ $g['username'] }}</a>
					</td>
					<td @if($s->name) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $s->name }}" @endif>
						{{ Str::words($s->name, 3, ' >>') }}
					</td>
					<td>{{ $g['dayt'] }}</td>
					<td>{!! $g['ll'] !!}</td>
					<td {!! ($g['l'])?$g['leaveIndicator']:null !!}>{!! $g['lea'] !!}</td>
					<td>{{ $g['date_fmt'] }}</td>
					<td>
						<span class="{{ $g['in_class'] }}">{{ $g['in_fmt'] }}</span>
					</td>
					<td>
						<span class="{{ $g['break_class'] }}">{{ $g['break_fmt'] }}</span>
					</td>
					<td>
						<span class="{{ $g['resume_class'] }}">{{ $g['resume_fmt'] }}</span>
					</td>
					<td>
						<span class="{{ $g['out_class'] }}">
							{{ $g['out_fmt'] }}
						</span>
					</td>
					<td>
						{{ ($s->time_work_hour != '00:00:00')?$s->time_work_hour:NULL }}
					</td>
					<td>
						{{ $g['ot_total'] }}
					</td>
					<td @if($g['os_customer']) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $g['os_customer'] }}" @endif>
						{{ ($g['os_customer'])?Str::limit($g['os_customer'], 8, ' >'):' ' }}
					</td>
					<td @if($s->remarks || $s->hr_remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $s->remarks }} {{ $s->hr_remarks }}" @endif>
						{{ Str::limit($s->remarks, 8, ' >') }}
						@if($s->hr_remarks)
							<br />
							<span class="text-danger">
								{{ Str::limit($s->hr_remarks, 8, ' >') }}
							</span>
						@endif
					</td>
					<td>
						{{ ($s->exception == 1)?'Yes':NULL }}
					</td>
					<td>
						<a href="{{ route('attendance.edit', $s->id) }}" class="btn btn-sm btn-outline-secondary">
							<i class="bi bi-pencil-square" style="font-size: 15px;"></i>
						</a>
					</td>
				</tr>
			@endif
			@endforeach
			</tbody>
		</table>

	</div>
</div>
@endsection

@section('js')
window.data = {};
@endsection
