		<nav class="navbar navbar-expand-lg ipma-navbar">
			<div class="container-fluid">

				<a href="{{ url('/') }}" class="navbar-brand d-flex align-items-center gap-2">
					<img src="{{ asset('images/logo.png') }}" class="img-fluid rounded" alt="Home" width="36" height="36">
					<span class="d-none d-md-inline">{!! config('app.name') !!}</span>
				</a>

					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor04" aria-controls="navbarColor04" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarColor04">
						<ul class="navbar-nav ms-auto">
							<li class="nav-item">
								<a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a>
							</li>
						</ul>
						@if (Route::has('login'))
							@auth
								<div class="dropdown">
									<a href="{{ url('/dashboard') }}" class="btn btn-sm btn-outline-secondary dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ Auth::user()->belongstostaff->name }}</a>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="{{ route('profile.show', Auth::user()->belongstostaff->id) }}"><i class="fa-regular fa-user"></i> Profile</a></li>
										<li><a class="dropdown-item" href="#"><i class="fa-regular fa-comment"></i> Notifications</a></li>
										<li><a class="dropdown-item" href="{{ route('leave.index') }}"><i class="fa-solid fa-mug-hot"></i> Apply Leave</a></li>
										<li><a class="dropdown-item" href="{{ route('outstationattendance.index') }}"><i class="fa-solid fa-user-plus"></i> Outstation Attendance</a></li>
										<li><a class="dropdown-item" href="{{ route('appraisalmark.index') }}"><i class="fa-solid fa-list"></i> Appraisal</a></li>
										<li>
											<form method="POST" action="{{ route('logout') }}">
												@csrf
												<a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"><i class="fas fa-light fa-right-from-bracket"></i> Log Out</a>
											</form>
										</li>
									</ul>
								</div>
							@else
								<a class="btn btn-sm btn-outline-secondary" href="{{ route('login') }}">Sign in</a>
							@endauth
						@endif
					</div>
				</div>
			</nav>
			<!-- end navigator -->
