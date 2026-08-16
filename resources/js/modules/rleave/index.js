// Replacement Leave (index) — datatable + delete confirmation
// window.data.url.base is injected by the blade (url('rleave'))
const { url } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#replacement').DataTable({ ...config.datatable,
    "lengthMenu": [ [100,250,500,-1], [100,250,500,"All"] ],
    "columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
	],
    "order": [ 2, 'desc' ],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip({ ...config.tooltip })
	});
});

$(function () {
	$('[data-toggle="tooltip"]').tooltip({ ...config.tooltip })
});

/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_replacement', function(e){
	var ackID = $(this).data('id');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackTable){
	swal.fire({ ...config.swal,
    title: 'Delete Replacement Leave',
    text: 'Are you sure to delete this replacement?',
    icon: 'info',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancel',
    confirmButtonText: 'Yes',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: url.base + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						table: ackTable,
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
})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancel Action', '', 'info')
		}
	});
}
