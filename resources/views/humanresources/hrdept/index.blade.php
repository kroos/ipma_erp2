@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')

	<div class="row g-3">

		<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2">
			<div>
				<h1 class="mb-1">HR Department Overview</h1>
				<p class="page-subtitle mb-0">Staff attendance summary for the last 6 working days</p>
			</div>
		</div>

		<!-- Stat cards (populated by JS from staffdaily) -->
		<div class="row g-3" id="stat-cards"></div>

		<div class="col-sm-12">
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between">
					<span>Overall Summary</span>
					<span class="badge text-bg-light" id="summary-period"></span>
				</div>
				<div class="card-body p-0">
					<div class="table-responsive">
						<table class="table table-hover table-sm align-middle mb-0" style="font-size:12px">
							<thead>
								<tr>
									<th class="text-center">Date</th>
									<th class="text-center">Day Status</th>
									<th class="text-center">Percentage</th>
									<th class="text-center">Available Staff</th>
									<th class="text-center" colspan="2">Outstation</th>
									<th class="text-center" colspan="2">On Leave</th>
									<th class="text-center" colspan="2">Absents</th>
									<th class="text-center" colspan="2">Half Absents</th>
									<th class="text-center">Total Staff</th>
								</tr>
							</thead>
							<tbody id="summary">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="card">
				<div class="card-header">Attendance Statistic Daily</div>
				<div class="card-body">
					<canvas id="myChart" width="200" height="75"></canvas>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
		staffdaily: "{{ route('staffdaily') }}",
	},
};

@endsection
