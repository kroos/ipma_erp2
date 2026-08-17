@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-staff-index container row justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h2>Staffs&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.create') }}"><i class="fa-solid fa-person-circle-plus fa-beat"></i> Add Staff</a></h2>
	<div class="col-sm-12 table-responsive">
		<table id="staff" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th>Staff ID</th>
					<th>No</th>
					<th>ID</th>
					<th>Name</th>
					<th>Group</th>
					<th>Nationality</th>
					<th>Marital Status</th>
					<th>Category</th>
					<th>Department</th>
					<th>Location</th>
					<th>Leave Flow</th>
					<th>Phone</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<h2>Inactive Staffs</h2>
	<div class="col-sm-12 table-responsive">
		<table id="inactivestaff" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th>Staff ID</th>
					<th>No</th>
					<th>ID</th>
					<th>Name</th>
					<th>Group</th>
					<th>Nationality</th>
					<th>Marital Status</th>
					<th>Category</th>
					<th>Department</th>
					<th>Location</th>
					<th>Leave Flow</th>
					<th>Phone</th>
				</tr>
			</thead>
			<tbody class="table-group-divider">
			</tbody>
		</table>
	</div>

	{{-- shared activate-ex-staff modal (wired by staff/index.js from the row data) --}}
	<div class="modal fade" id="activateStaffModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="activateStaffModalLabel">Activate Ex-Staff</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form method="POST" action="" accept-charset="UTF-8" id="form" autocomplete="off" enctype="multipart/form-data">
					@csrf
					@method('PATCH')
					<div class="modal-body">
						<div class="form-group row mb-3">
							<label for="user" class="col-form-label col-sm-3">Username : </label>
							<div class="col-auto" style="position: relative">
								<input type="text" name="username" value="{{ old('username') }}" id="user" class="form-control form-control-sm" placeholder="Username" required>
							</div>
						</div>
						<div class="form-group row mb-3">
							<label for="pass" class="col-form-label col-sm-3">Password : </label>
							<div class="col-auto" style="position: relative">
								<input type="password" name="password" value="{{ old('password') }}" id="pass" class="form-control form-control-sm col-sm-12" placeholder="Password" required>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-sm btn-outline-secondary">Save Changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {},
	url: {
		table: '{{ route('api.staff.table') }}',
		image: '{{ asset('storage/user_profile') }}',
		activate: '{{ url('api/staffactivate') }}',
	},
	old: {},
	isAdmin: @json($isAdmin),
	errors: @json($errors->toArray()),
};
@endsection
