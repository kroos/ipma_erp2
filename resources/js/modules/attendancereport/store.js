// Attendance Report (store) — date range pickers + attendance staff datatable

/////////////////////////////////////////////////////////////////////////////////////////
// datepicker
$('#from').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
})
.on('dp.change dp.update', function(e) {

});

$('#to').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
})
.on('dp.change dp.update', function(e) {

});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#attendancestaff_').DataTable({ ...config.datatable,
    "columnDefs": [
					{ type: 'date', 'targets': [3] },
					{ type: 'time', 'targets': [4] },
					{ type: 'time', 'targets': [5] },
					{ type: 'time', 'targets': [6] },
					{ type: 'time', 'targets': [7] },
				],
    "lengthMenu": [ [-1], ["All"] ],
    "order": [[3, "asc" ]],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});}
);
