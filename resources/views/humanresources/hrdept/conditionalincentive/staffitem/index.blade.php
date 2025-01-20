@extends('layouts.app')

@push('styles')
	@livewireStyles
@endpush

@push('scripts')
	@livewireScripts
@endpush

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h2>Staff with Conditional Incentive</h2>

	<div class="hstack align-items-start justify-content-between">
		<div class="col-sm-12 m-3">
			<h4>Create Incentive With Staff</h4>
			@livewire('HumanResources.HRDept.CICategoryItemStaffCreate')
		</div>
	</div>

	@livewire('HumanResources.HRDept.CICategoryItemStaff')
</div>
@endsection


@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
//tooltip
// $(document).ready(function(){
// 	$('[data-bs-toggle="tooltip"]').tooltip();
// });
//
// /////////////////////////////////////////////////////////////////////////////////////////
// // datatables
// $.fn.dataTable.moment( 'D MMM YYYY' );
// $.fn.dataTable.moment( 'h:mm a' );
// $('#category').DataTable({
// 	"paging": true,
// 	"lengthMenu": [ [25,50,100,-1], [25,50,100,"All"] ],
// 	// "columnDefs": [
// 	// 				{ type: 'date', 'targets': [2] },
// 	// 				{ type: 'time', 'targets': [3] },
// 	// ],
// 	"order": [ 0, 'desc' ], // sorting the column descending
// 	responsive: true
// }).on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
// 	$(document).ready(function(){
// 		$('[data-bs-toggle="tooltip"]').tooltip();
// 	});}
// );
//
// /////////////////////////////////////////////////////////////////////////////////////////
// // DELETE
// $(document).on('click', '.delete_discipline', function(e){
// 	var ackID = $(this).data('id');
// 	var ackSoftcopy = $(this).data('softcopy');
// 	var ackTable = $(this).data('table');
// 	SwalDelete(ackID, ackSoftcopy, ackTable);
// 	e.preventDefault();
// });
//
// function SwalDelete(ackID, ackSoftcopy, ackTable){
// 	swal.fire({
// 		title: 'Delete Discipline',
// 		text: 'Are you sure to delete this discipline?',
// 		icon: 'info',
// 		showCancelButton: true,
// 		confirmButtonColor: '#3085d6',
// 		cancelButtonColor: '#d33',
// 		cancelButtonText: 'Cancel',
// 		confirmButtonText: 'Yes',
// 		showLoaderOnConfirm: true,
//
// 		preConfirm: function() {
// 			return new Promise(function(resolve) {
// 				$.ajax({
// 					url: '{{ url('discipline') }}' + '/' + ackID,
// 					type: 'DELETE',
// 					dataType: 'json',
// 					data: {
// 						id: ackID,
// 						softcopy: ackSoftcopy,
// 						table: ackTable,
// 						_token : $('meta[name=csrf-token]').attr('content')
// 					},
// 				})
// 				.done(function(response){
// 					swal.fire('Accept', response.message, response.status)
// 					.then(function(){
// 						window.location.reload(true);
// 					});
// 				})
// 				.fail(function(){
// 					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
// 				})
// 			});
// 		},
// 		allowOutsideClick: false
// 	})
// 	.then((result) => {
// 		if (result.dismiss === swal.DismissReason.cancel) {
// 			swal.fire('Cancel Action', '', 'info')
// 		}
// 	})
// };
// //auto refresh right after clicking OK button
// $(document).on('click', '.swal2-confirm', function(e){
// 	window.location.reload(true);
// });
@endsection


@section('nonjquery')

@endsection
