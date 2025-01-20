<!-- <nav class="nav nav-underline justify-content-between"> -->
<nav class="nav nav-underline justify-content-center">
	@auth
		<a class="nav-item nav-link link-body-emphasis {{ (request()->route()->uri == 'hrdept')?'active':NULL }}" href="{{ route('hrdept.index') }}">HR Department</a>
		<a class="nav-item nav-link link-body-emphasis {{ (request()->route()->uri == 'salesdept')?'active':NULL }}" href="{{ route('salesdept.index') }}">Sales Department</a>
		<a class="nav-item nav-link link-body-emphasis {{ (request()->route()->uri == 'costingdept')?'active':NULL }}" href="{{ route('costingdept.index') }}">Costing Department</a>
	@else
		<a class="nav-item nav-link link-body-emphasis" href="#">Announcement</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Scan Job</a>
	@endauth
</nav>
