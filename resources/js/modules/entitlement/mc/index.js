const { route, url, old } = window.data;

$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#mcl').DataTable({ ...config.datatable,
    paging: false,
    order: [ 0, 'asc' ],
})
.on( 'length.dt page.dt order.dt search.dt', function () {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

$(document).on('click', '#genal', function(e){
	e.preventDefault();
	swal.fire({ ...config.swal,
    text: 'System will generate Medical Certificate Leave Entitlement for each of active staff',
    icon: 'info',
    confirmButtonText: 'Yes, generate it!',
    preConfirm: async () => {
			try {
				return await $.ajax({
					url: route.generatemcleave,
					type: 'POST',
					data: {},
					dataType: 'json'
				});
			} catch (err) {
				swal.showValidationMessage('Request failed');
			}
		},
}).then((result) => {
		if (result.isConfirmed && result.value) {
			swal.fire('Done!', result.value.message, result.value.status)
			.then(() => window.location.reload());
		}
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'System did not generate MC Leave Entitlements.', 'info');
		}
	});
});
