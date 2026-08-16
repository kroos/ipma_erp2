const { route, url, old } = window.data;

$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#inactive,#active').DataTable({ ...config.datatable,
    paging: false,
    columnDefs: [
		{ type: 'date', 'targets': [5,6] },
	],
    order: [ 5, 'desc' ],
})
.on( 'length.dt page.dt order.dt search.dt', function () {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});
