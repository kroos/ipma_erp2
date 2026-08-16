const { route, url, old } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* datatables */
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('h:mm a');
var table = $('#attendanceremark').DataTable({ ...config.datatable,
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});
});

/* delete remark */
$(document).on('click', '.remark-delete', async function (e) {
	e.preventDefault();
	const id = $(this).data('id');

	try {
		const result = await swal.fire({ ...config.swal,
    preConfirm: async () => {
				try {
					const response = await $.ajax({
						url: `${url.attendanceremark}/${id}`,
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
			/* client-side table: remove the deleted row in place */
			table.row($(e.currentTarget).closest('tr')).remove().draw(false);
		}

		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Your data is safe from delete', 'info');
		}

	} catch (err) {
		swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
	}
});