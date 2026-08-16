const { route, url, old, errors } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
var table = $('#discipline').DataTable({ ...config.datatable,
    "lengthMenu": [ [25,50,100,-1], [25,50,100,"All"] ],
    "columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
	],
    "order": [ 2, 'desc' ],
});


$(function () {
	$('[data-toggle="tooltip"]').tooltip({ ...config.tooltip })
});


/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_discipline', function(e){
	var ackID = $(this).data('id');
	var ackSoftcopy = $(this).data('softcopy');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackSoftcopy, ackTable, $(this));
	e.preventDefault();
});

function SwalDelete(ackID, ackSoftcopy, ackTable, btn){
	swal.fire({ ...config.swal,
    title: 'Delete Discipline',
    text: 'Are you sure to delete this discipline?',
    icon: 'info',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancel',
    confirmButtonText: 'Yes',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: url.discipline + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						softcopy: ackSoftcopy,
						table: ackTable,
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						table.row(btn.closest('tr')).remove().draw(false);
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
	})
};
// //auto refresh right after clicking OK button
// $(document).on('click', '.swal2-confirm', function(e){
// 	window.location.reload(true);
// });
