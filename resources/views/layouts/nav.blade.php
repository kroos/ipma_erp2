<!-- <nav class="nav nav-underline justify-content-between"> -->
<nav class="nav dept-nav justify-content-center gap-1">
	@auth
		<a class="nav-link {{ (request()->route()->uri == 'hrdept')?'active':NULL }}" href="{{ route('hrdept.index') }}"><i class="fa-solid fa-users me-1"></i> HR Department</a>
		<a class="nav-link {{ (request()->route()->uri == 'salesdept')?'active':NULL }}" href="{{ route('salesdept.index') }}"><i class="fa-solid fa-chart-line me-1"></i> Sales Department</a>
		<a class="nav-link {{ (request()->route()->uri == 'costingdept')?'active':NULL }}" href="{{ route('costingdept.index') }}"><i class="fa-solid fa-calculator me-1"></i> Costing Department</a>
		<a class="nav-link {{ (request()->route()->uri == 'activity-logs')?'active':NULL }}" href="{{ route('activity-logs.index') }}"><i class="fa-solid fa-clock-rotate-left me-1"></i> Activity Log</a>
	@else
<!-- 		<a class="nav-item nav-link link-body-emphasis" href="#">Announcement</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Scan Job</a> -->
	@endauth
</nav>
