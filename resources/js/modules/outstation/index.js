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
$('#nowoutstation,#lastoutstation').DataTable({ ...config.datatable,
    "lengthMenu": [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
    "columnDefs": [ { type: 'date', 'targets': [3, 4] } ],
    "order": [[4, "desc"], [3, "desc"]],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// ajax post delete row
$(document).on('click', '.delete_button', function(e){

	var outId = $(this).data('id');
	var $row = $(this).closest('tr');
	var $table = $(this).closest('table');
	SwalDelete(outId, $table, $row);
	e.preventDefault();
});

function SwalDelete(outId, $table, $row){
	swal.fire({ ...config.swal,
    type: 'warning',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: url.outstation + '/' + outId,
					type: 'DELETE',
					data: {
							id: outId,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Deleted!', response.message, response.status)
					.then(function(){
						$table.DataTable().row($row).remove().draw(false);
					});
					//$('#delete_product_' + outId).parent().parent().remove();
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
				})
			});
		},
})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Your data is safe from delete', 'info')
		}
	});
}
