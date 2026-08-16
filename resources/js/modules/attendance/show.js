/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#staff').DataTable({ ...config.datatable,
    "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
    "order": [[1, "asc" ]],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});}
);
