@extends('layouts.app')

@section('fullscreen')
	<div class="login-page w-100">
		<form method="POST" action="{{ route('register') }}" id='form' class="needs-validation card login-card" autocomplete="off" enctype="multipart/form-data">
			@csrf
			<div class="card-header">
				<img src="{{ asset('images/logo.png') }}" class="brand-mark" alt="logo">
				<h3>Create Account</h3>
				<p class="m-0 mt-2 opacity-75 small">{!! config('app.name') !!}</p>
			</div>
			<div class="card-body">

				<div class="mb-3 @error('name') has-error @enderror">
					<label for="name" class="form-label">Full Name</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fa-regular fa-user"></i></span>
						<input type="text" name="name" value="{{ old('name') }}" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter your full name">
					</div>
					@error('name')
						<div class="invalid-feedback d-block">
							{{ $message }}
						</div>
					@enderror
				</div>

				<div class="mb-3 @error('email') has-error @enderror">
					<label for="email" class="form-label">Email</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
						<input type="email" name="email" value="{{ old('email') }}" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email">
					</div>
					@error('email')
						<div class="invalid-feedback d-block">
							{{ $message }}
						</div>
					@enderror
				</div>

				<div class="mb-3 @error('password') has-error @enderror">
					<label for="password" class="form-label">Password</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
						<input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Create a password">
					</div>
					@error('password')
						<div class="invalid-feedback d-block">
							{{ $message }}
						</div>
					@enderror
				</div>

				<div class="mb-3 @error('password_confirmation') has-error @enderror">
					<label for="password_confirmation" class="form-label">Confirm Password</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
						<input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" placeholder="Re-enter your password">
					</div>
					@error('password_confirmation')
						<div class="invalid-feedback d-block">
							{{ $message }}
						</div>
					@enderror
				</div>

			</div>
			<div class="card-footer">

				<div class="d-grid">
					<button type="submit" class="btn btn-primary">Create Account</button>
					<div class="text-center mt-3">
						<a class="small" href="{{ route('login') }}">
							{{ __('Already registered? Sign in') }}
						</a>
					</div>
				</div>

			</div>
		</form>
	</div>
@endsection

@section('js')
@endsection
