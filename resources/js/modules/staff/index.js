// tooltip
$(document).ready(function () {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

// datatables
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('D MMM YYYY h:mm a');
$('#staff, #inactivestaff').DataTable({ ...config.datatable,
})
	.on('length.dt page.dt order.dt search.dt', function (e, settings, len) {
		$(document).ready(function () {
			$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
		});
	});
