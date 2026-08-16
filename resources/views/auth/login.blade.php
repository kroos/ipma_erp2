@extends('layouts.app')

@section('fullscreen')
	<div class="login-page w-100">
		<form method="POST" action="{{ route('login') }}" id='form' class="needs-validation card login-card" autocomplete="off" enctype="multipart/form-data">
			@csrf
			<div class="card-header">
				<img src="{{ asset('images/logo.png') }}" class="brand-mark" alt="logo">
				<h3>Sign In</h3>
				<p class="m-0 mt-2 opacity-75 small">{!! config('app.name') !!}</p>
			</div>
			<div class="card-body">

				<div class="mb-3 @error('username') has-error @enderror">
					<label for="username" class="form-label">Username</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fa-regular fa-user"></i></span>
						<input type="text" name="username" value="{{ old('username') }}" id="username" class="form-control @error('username') is-invalid @enderror" placeholder="Enter your username">
					</div>
					@error('username')
						<div class="invalid-feedback d-block">
							{{ $message }}
						</div>
					@enderror
				</div>

				<div class="mb-3 @error('password') has-error @enderror">
					<label for="password" class="form-label">Password</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
						<input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Enter your password">
					</div>
					@error('password')
						<div class="invalid-feedback d-block">
							{{ $message }}
						</div>
					@enderror
				</div>

				<div class="mb-3">
					<div class="pretty p-svg p-round p-plain p-jelly">
						<input type="checkbox" name="remember" value="{{ old('remember') }}" class="form-check-input form-check-input-sm" id="remember_me">
						<div class="state p-success">
							<span class="svg"><i class="bi bi-check"></i></span>
							<label for="remember_me">{{ __('Remember me') }}</label>
						</div>
					</div>
				</div>

			</div>
			<div class="card-footer">

				<div class="d-grid">
					<button type="submit" class="btn btn-primary">Login</button>
					@if (Route::has('password.request'))
						<div class="text-center mt-3">
							<a class="small" href="{{ route('password.request') }}">
								{{ __('Forgot your password?') }}
							</a>
						</div>
					@endif
				</div>

			</div>
		</form>
	</div>
@endsection

@section('js')
@endsection
