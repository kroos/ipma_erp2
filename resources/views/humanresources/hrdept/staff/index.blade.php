@extends('layouts.app')

@section('content')
<?php
use App\Models\Staff;
?>
<div class="container row justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h2>Staffs&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.create') }}"><i class="fa-solid fa-person-circle-plus fa-beat"></i> Add Staff</a></h2>
	<div class="col-sm-12 table-responsive">
		<table id="staff" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					@if(auth()->user()->belongstostaff->authorise_id == 1)
					<th>Staff ID</th>
					@endif
					<th>No</th>
					<th>ID</th>
					<th>Name</th>
					<th>Group</th>
					<!-- <th>Gender</th> -->
					<th>Nationality</th>
					<th>Marital Status</th>
					<th>Category</th>
					<th>Department</th>
					<th>Location</th>
					<th>Leave Flow</th>
					<th>Phone</th>
				<!-- <th>CIMB Acc</th>
					<th>EPF</th>
					<th>Income Tax</th>
					<th>SOCSO</th>
					<th>Join</th>
					<th>Confirmed</th> -->
				</tr>
			</thead>
			<tbody>
<?php
// who am i?
$me1 = \Auth::user()->belongstostaff->div_id == 1;		// hod
$me2 = \Auth::user()->belongstostaff->div_id == 5;		// hod assistant
$me3 = \Auth::user()->belongstostaff->div_id == 4;		// supervisor
$me4 = \Auth::user()->belongstostaff->div_id == 3;		// HR
$me5 = \Auth::user()->belongstostaff->authorise_id == 1;	// admin
$me6 = \Auth::user()->belongstostaff->div_id == 2;		// director
$dept = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;
$r = 1;
?>
				@foreach(Staff::where('active', 1)->get() as $s)
<?php
if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 16 || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	}
} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
	$ha = true;
} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
	$ha = true;
} else {
	$ha = false;
}
?>
					@if( $ha )
						<tr>
							@if(auth()->user()->belongstostaff->authorise_id == 1)
							<td>{{ $s->id }}</td>
							@endif
							<td>{{ $r++ }}</td>
							<td><a href="{{ route('staff.show', $s->id) }}" alt="Detail" title="Detail">{{ $s->hasmanylogin()->where('active', 1)->first()?->username }}</a></td>
							<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="
								<div class='d-flex flex-column align-items-center text-center p-3 py-5'>
									<img class='rounded-5 mt-3' width='180px' src='{{ asset('storage/user_profile/' . $s->image) }}'>
									<span class='font-weight-bold'>{{ $s->name }}</span>
									<span class='font-weight-bold'>{{ $s->hasmanylogin()->where('active', 1)->first()?->username }}</span>
									<span> </span>
								</div>
							">{{ $s->name }}</td>
							<td>{{ $s->belongstorestdaygroup?->group }}</td>
							<!-- <td>{{ $s->belongstogender?->gender }}</td> -->
							<td>{{ $s->belongstonationality?->country }}</td>
							<td>{{ $s->belongstomaritalstatus?->marital_status }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->belongstocategory->category }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->department }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->belongstobranch->location }}</td>
							<td>{{ $s->belongstoleaveapprovalflow?->description }}</td>
							<td>{{ $s->mobile }}</td>
		<!-- 				<td>{{ $s->cimb_account }}</td>
							<td>{{ $s->epf_account }}</td>
							<td>{{ $s->income_tax_no }}</td>
							<td>{{ $s->socso_no }}</td>
							<td>{{ \Carbon\Carbon::parse($s->join)->format('j M Y ') }}</td>
							<td>{{ \Carbon\Carbon::parse($s->confirmed)->format('j M Y ') }}</td> -->
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<h2>Inactive Staffs</h2>
	<div class="col-sm-12 table-responsive">

		<table id="inactivestaff" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					@if(auth()->user()->belongstostaff->authorise_id == 1)
					<th>Staff ID</th>
					@endif
					<th>No</th>
					<th>ID</th>
					<th>Name</th>
					<th>Group</th>
					<!-- <th>Gender</th> -->
					<th>Nationality</th>
					<th>Marital Status</th>
					<th>Category</th>
					<th>Department</th>
					<th>Location</th>
					<th>Leave Flow</th>
					<th>Phone</th>
				<!-- <th>CIMB Acc</th>
					<th>EPF</th>
					<th>Income Tax</th>
					<th>SOCSO</th>
					<th>Join</th>
					<th>Confirmed</th> -->
				</tr>
			</thead>
			<tbody class="table-group-divider">
<?php
// who am i?
$me1 = \Auth::user()->belongstostaff->div_id == 1;		// hod
$me2 = \Auth::user()->belongstostaff->div_id == 5;		// hod assistant
$me3 = \Auth::user()->belongstostaff->div_id == 4;		// supervisor
$me4 = \Auth::user()->belongstostaff->div_id == 3;		// HR
$me5 = \Auth::user()->belongstostaff->authorise_id == 1;	// admin
$me6 = \Auth::user()->belongstostaff->div_id == 2;		// director
$dept = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;
$t = 1;
?>
				@foreach(Staff::where('active', '<>', 1)->get() as $s)
<?php
if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 16 || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	}
} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
	$ha = true;
} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
	$ha = true;
} else {
	$ha = false;
}

?>
					@if( $ha )
						<tr>
							@if(auth()->user()->belongstostaff->authorise_id == 1)
							<td>{{ $s->id }}</td>
							@endif
							<td>{{ $t++ }}</td>
							<td><a href="{{ route('staff.show', $s->id) }}" alt="Detail" title="Detail">{{ $s->hasmanylogin()->where('active', 1)->first()?->username }}</a></td>
							<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="
								<div class='d-flex flex-column align-items-center text-center p-3 py-5'>
									<img class='rounded-5 mt-3' width='180px' src='{{ asset('storage/user_profile/' . $s->image) }}'>
									<span class='font-weight-bold'>{{ $s->name }}</span>
									<span class='font-weight-bold'>{{ $s->hasmanylogin()->where('active', 1)->first()?->username }}</span>
									<span> </span>
								</div>
							">
								<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#staff_{{ $s->id }}">
									{{ $s->name }}
								</button>
								<div class="modal fade" id="staff_{{ $s->id }}" tabindex="-1" aria-labelledby="label_{{ $s->id }}" aria-hidden="true" data-backdrop="false">
									<div class="modal-dialog modal-dialog-centered">
										<div class="modal-content">
											<div class="modal-header">
												<h1 class="modal-title fs-5" id="label_{{ $s->id }}">Activate Ex-Staff {{ $s->name }}</h1>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												{{ Form::model($s, ['route' => ['staff.activate', $s->id], 'method' => 'PATCH', 'id' => 'form', 'files' => true, ]) }}
												<div class="form-group row mb-3 {{ $errors->has('username') ? 'has-error' : '' }}">
													{{ Form::label( 'user', 'Username : ', ['class' => 'col-sm-3 col-form-label'] ) }}
													<div class="col-auto" style="position: relative">
														{{ Form::text('username', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'user', 'placeholder' => 'Username', 'autocomplete' => 'off']) }}
													</div>
												</div>
												<div class="form-group row mb-3 {{ $errors->has('password') ? 'has-error' : '' }}">
													{{ Form::label( 'pass', 'Password : ', ['class' => 'col-sm-3 col-form-label'] ) }}
													<div class="col-auto" style="position: relative">
														{{ Form::text('password', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'pass', 'placeholder' => 'Password', 'autocomplete' => 'off']) }}
													</div>
												</div>
											<!-- 	<div class="form-group row mb-3 g-3 p-2">
													<div class="col-sm-10 offset-sm-2">
														{!! Form::button('Edit Data', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
													</div>
												</div> -->
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
												<button type="submit" class="btn btn-sm btn-outline-secondary">Save Changes</button>
											</div>
												{{ Form::close() }}
										</div>
									</div>
								</div>


							</td>
							<td>{{ $s->belongstorestdaygroup?->group }}</td>
							<!-- <td>{{ $s->belongstogender?->gender }}</td> -->
							<td>{{ $s->belongstonationality?->country }}</td>
							<td>{{ $s->belongstomaritalstatus?->marital_status }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->belongstocategory->category }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->department }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->belongstobranch->location }}</td>
							<td>{{ $s->belongstoleaveapprovalflow?->description }}</td>
							<td>{{ $s->mobile }}</td>
		<!-- 				<td>{{ $s->cimb_account }}</td>
							<td>{{ $s->epf_account }}</td>
							<td>{{ $s->income_tax_no }}</td>
							<td>{{ $s->socso_no }}</td>
							<td>{{ \Carbon\Carbon::parse($s->join)->format('j M Y ') }}</td>
							<td>{{ \Carbon\Carbon::parse($s->confirmed)->format('j M Y ') }}</td> -->
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>

	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#staff, #inactivestaff').DataTable({
	"lengthMenu": [ [-1], ["All"] ],
	"order": [[1, "asc" ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
@endsection
