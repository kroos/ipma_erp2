const { route, url, old, errors } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#staff').DataTable({ ...config.datatable,
    "paging": false,
    "order": [ 0, 'asc' ],
    "columnDefs": [
		{ type: 'string', 'targets': [0] },
		{ type: 'string', 'targets': [1] },
	],
});

$(function () {
	$('[data-toggle="tooltip"]').tooltip({ ...config.tooltip })
});
