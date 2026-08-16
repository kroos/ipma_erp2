const { route, url, old, errors } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#overtime').DataTable({ ...config.datatable,
    "lengthMenu": [ [10,25,50,100,150,200,-1], [10,25,50,100,150,200,"All"] ],
    "columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
					{ type: 'time', 'targets': [4] },
				],
    "order": [[2, "DESC" ]],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_overtime', function(e){
	var ackID = $(this).data('id');
	SwalDelete(ackID);
	e.preventDefault();
});

function SwalDelete(ackID, ackSoftcopy, ackTable){
	swal.fire({ ...config.swal,
    title: 'Delete Overtime',
    text: 'Are you sure to delete this overtime?',
    icon: 'info',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancel',
    confirmButtonText: 'Yes',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: url.overtime + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
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
	})
};
