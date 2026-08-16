@extends('layouts.app')

@section('content')
<div class="col-sm-12">
	<div class="row g-4">

		<!-- Page header -->
		<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2">
			<div>
				<h1 class="mb-1">Welcome back</h1>
				<p class="page-subtitle mb-0">Select a department to get started.</p>
			</div>
		</div>

		<!-- Quick launch -->
		<div class="col-sm-12">
			<div class="row g-3">
				<div class="col-md-4">
					<a href="{{ route('hrdept.index') }}" class="text-decoration-none">
						<div class="card h-100 launch-card accent-green">
							<div class="card-body d-flex align-items-start gap-3">
								<span class="stat-icon"><i class="fa-solid fa-users"></i></span>
								<div>
									<div class="stat-label">HR Department</div>
									<p class="card-text text-muted mb-0 small">Staff, attendance, leave and appraisal management.</p>
								</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-md-4">
					<a href="{{ route('salesdept.index') }}" class="text-decoration-none">
						<div class="card h-100 launch-card accent-blue">
							<div class="card-body d-flex align-items-start gap-3">
								<span class="stat-icon"><i class="fa-solid fa-chart-line"></i></span>
								<div>
									<div class="stat-label">Sales Department</div>
									<p class="card-text text-muted mb-0 small">Sales tracking, targets and performance.</p>
								</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-md-4">
					<a href="{{ route('costingdept.index') }}" class="text-decoration-none">
						<div class="card h-100 launch-card accent-amber">
							<div class="card-body d-flex align-items-start gap-3">
								<span class="stat-icon"><i class="fa-solid fa-calculator"></i></span>
								<div>
									<div class="stat-label">Costing Department</div>
									<p class="card-text text-muted mb-0 small">Costing, pricing and margin analysis.</p>
								</div>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>

		<!-- Activity log -->
		<div class="col-sm-12">
			<a href="{{ route('activity-logs.index') }}" class="text-decoration-none">
				<div class="card launch-card accent-red">
					<div class="card-body d-flex align-items-center gap-3">
						<span class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
						<div>
							<div class="stat-label">Activity Log</div>
							<p class="card-text text-muted mb-0 small">Review a full audit trail of system changes.</p>
						</div>
					</div>
				</div>
			</a>
		</div>

	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
