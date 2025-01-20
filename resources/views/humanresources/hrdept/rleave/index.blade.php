@extends('layouts.app')

@section('content')
<?php
// load sql builder
use Illuminate\Database\Eloquent\Builder;
?>
<style>
	.btn-sm-custom {
		padding: 0px;
		background: none;
		border: none;
		font-size: 15px;
		width: 100%;
		height: 100%;
	}
</style>

<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Replacement Leave&nbsp; <a class="btn btn-sm btn-outline-secondary" href="{{ route('rleave.create') }}"><i class="fa-solid fa-person-walking-arrow-loop-left fa-beat"></i> Add Replacement Leave</a></h4>
	<div class="col-sm-12 row table-responsive">
		<table id="replacement" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Date Start</th>
					<th>Date End</th>
					<th>Customer</th>
					<th>Reason</th>
					<th>Total</th>
					<th>Utilize</th>
					<th>Balance</th>
					<th>Replacement Leave</th>
					<th>Remarks</th>
					<th>Edit</th>
					<th>Cancel</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($replacements as $replacement)
				<tr>
					<td>{{ $replacement->belongstostaff?->hasmanylogin()->where('logins.active', 1)->first()?->username }}</td>
					<td class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $replacement->belongstostaff?->name }}">{{ $replacement->belongstostaff?->name }}</td>
					<td>{{ \Carbon\Carbon::parse($replacement->date_start)->format('j M Y') }}</td>
					<td>{{ \Carbon\Carbon::parse($replacement->date_end)->format('j M Y') }}</td>
					<td class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $replacement->belongstocustomer?->customer }}">{{ Str::limit($replacement->belongstocustomer?->customer, 10, '>') }}</td>
					<td class="text-truncate" style="max-width: 150px;" data-toggle="tooltip" title="{{ $replacement->reason }}">{{ Str::limit($replacement->reason, 10, '>') }}</td>
					<td class="text-center">{{ $replacement->leave_total }}</td>
					<td class="text-center">{{ $replacement->leave_utilize }}</td>
					<td class="text-center">{{ $replacement->leave_balance }}</td>
					<td class="text-center">
						<?php
						if ($replacement->belongstomanyleave()->count()) {
							foreach ($replacement->belongstomanyleave()->get() as $val) {
								echo '<a href="'.route('hrleave.show', $val->id).'">HR9-'.str_pad($val->leave_no, 5, "0", STR_PAD_LEFT ).'/'.$val->leave_year.'</a><br />';
							}
						}
						?>
					</td>
					<td class="text-truncate" style="max-width: 100px;" data-toggle="tooltip" title="{{ $replacement->remarks }}">{{ Str::limit($replacement->remarks, 10, '>') }}</td>
					<td class="text-center">
						<a href="{{ route('rleave.edit', $replacement->id) }}" class="btn btn-sm btn-outline-secondary">
							<i class="fa-regular fa-pen-to-square"></i>
						</a>
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-outline-secondary delete_replacement" data-id="{{ $replacement->id }}" data-table="replacement" >
							<i class="fa-regular fa-trash-can"></i>
						</button>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>

		<div class="d-flex justify-content-center">
			{{ $replacements->links() }}
		</div>
	</div>
</div>
@endsection


@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#replacement').DataTable({
	"lengthMenu": [ [100,250,500,-1], [100,250,500,"All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
	],
	"order": [ 2, 'desc' ], // sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip()
	});
});

$(function () {
	$('[data-toggle="tooltip"]').tooltip()
})


/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_replacement', function(e){
	var ackID = $(this).data('id');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackTable){
	swal.fire({
		title: 'Delete Replacement Leave',
		text: 'Are you sure to delete this replacement?',
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancel',
		confirmButtonText: 'Yes',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('rleave') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						table: ackTable,
						_token : $('meta[name=csrf-token]').attr('content')
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancel Action', '', 'info')
		}
	});
}
@endsection


@section('nonjquery')

@endsection
