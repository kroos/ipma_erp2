const { route, url, old } = window.data;

/* tooltip */
$(document).ready(function(){
	// $('[data-bs-toggle="tooltip"]').tooltip();
});

/* datatables */
var table = $('#salescustomer').DataTable({ ...config.datatable,
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		// $('[data-bs-toggle="tooltip"]').tooltip();
	});
});

/* delete customer */
$(document).on('click', '.customer-delete', async function (e) {
	e.preventDefault();
	const id = $(this).data('id');

	try {
		const result = await swal.fire({ ...config.swal,
    preConfirm: async () => {
				try {
					const response = await $.ajax({
						url: `${url.salescustomer}/${id}`,
						type: 'DELETE',
						dataType: 'json',
						data: { id }
					});
					return response;
				} catch (err) {
					swal.showValidationMessage('Request failed');
				}
			},
});

		if (result.isConfirmed && result.value) {
			await swal.fire(
				result.value.message,
				'',
				result.value.status
			);
			table.ajax.reload(null, false);
		}

		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Your data is safe from delete', 'info');
		}

	} catch (err) {
		swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
	}
});
