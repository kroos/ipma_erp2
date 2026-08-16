const { route, url, old, errors } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* datatables */
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('h:mm a');
$('#mcl').DataTable({ ...config.datatable,
    "lengthMenu": [ [-1], ["All"] ],
    "order": [ 0, 'asc' ],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});
});

/* generate mc leave */
$(document).on('click', '#genal', async function (e) {
	e.preventDefault();

	try {
		const result = await swal.fire({ ...config.swal,
    text: 'System will generate Medical Certificate Leave Entitlement for each of active staff',
    icon: 'info',
    confirmButtonText: 'Yes, generate it!',
    preConfirm: async () => {
				try {
					const response = await $.ajax({
						url: route.generatemcleave,
						type: 'POST',
						dataType: 'json',
						data: {}
					});
					return response;
				} catch (err) {
					swal.showValidationMessage('Request failed');
				}
			},
});

		if (result.isConfirmed && result.value) {
			await swal.fire('Done!', result.value.message, result.value.status);
			window.location.reload();
		}

		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'System did not generate Medical Certificate Leave Entitlements.', 'info');
		}

	} catch (err) {
		swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
	}
});
