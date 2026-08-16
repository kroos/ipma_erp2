const { route, url, old } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* datatables */
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('D MMM YYYY h:mm a');
var table = $('#outstationcustomer').DataTable({ ...config.datatable,
    "lengthMenu": [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
    "columnDefs": [ { type: 'date', 'targets': [3, 4] } ],
    "order": [[4, "desc"], [3, "desc"]],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});
});

/* delete customer */
$(document).on('click', '.customer-delete', async function (e) {
	e.preventDefault();
	const id = $(this).data('id');

	try {
		const result = await swal.fire({ ...config.swal,
    type: 'warning',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    preConfirm: async () => {
				return new Promise(function(resolve) {
					$.ajax({
						url: `${url.outstationcustomer}/${id}`,
						type: 'DELETE',
						data: {
								id: id,
						},
						dataType: 'json'
					})
					.done(function(response){
						resolve(response);
					})
					.fail(function(){
						swal.showValidationMessage('Something went wrong with ajax !');
					})
				});
			},
});

		if (result.isConfirmed && result.value) {
			await swal.fire(
				result.value.message,
				'',
				result.value.status
			);
			/* client-side table: remove the deleted row in place */
			table.row($(e.currentTarget).closest('tr')).remove().draw(false);
		}

		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Your data is safe from delete', 'info');
		}

	} catch (err) {
		swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
	}
});