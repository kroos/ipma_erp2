const { route, url, old, errors } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* datatables */
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#upleave').DataTable({ ...config.datatable,
    "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
    "columnDefs": [
					{ type: 'date', 'targets': [4,5,6] },
					// { type: 'time', 'targets': [6] },
				],
    "order": [ 5, 'desc' ],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});}
);

$('#toleave').DataTable({ ...config.datatable,
    "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
    "columnDefs": [
					{ type: 'date', 'targets': [4,5,6] },
					// { type: 'time', 'targets': [6] },
				],
    "order": [ 5, 'desc' ],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});}
);

$('#paleave').DataTable({ ...config.datatable,
    "lengthMenu": [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
    "columnDefs": [
					{ type: 'date', 'targets': [4,5,6] },
					// { type: 'time', 'targets': [6] },
				],
    "order": [ 5, 'desc' ],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});}
);
