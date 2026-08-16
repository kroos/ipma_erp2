const { route, url, old, errors } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* datatables */
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('h:mm a');
$('#workinghour').DataTable({ ...config.datatable,
    "paging": false,
    "columnDefs": [
		{ type: 'date', targets: [5, 6] },  // effective date from / to
	],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});
});
