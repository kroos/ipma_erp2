const { route, url, old, errors } = window.data;
const csrf = $('meta[name=csrf-token]').attr('content');

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

/* fullcalendar cant use jquery */
var calendarEl = document.getElementById('calendar');
var calendar = new Calendar(calendarEl, {
	aspectRatio: 1.0,
	height: 2000,
	initialView: 'multiMonthYear',
	plugins: [
		timeGridPlugin,
		dayGridPlugin,
		multiMonthPlugin,
		momentPlugin,
		bootstrap5Plugin
	],
	// multiMonthMaxColumns: 1,					// force a single column
	headerToolbar: {
		left: 'prev,next today',
		center: 'title',
		right: 'multiMonthYear,dayGridMonth,timeGridWeek'
	},
	weekNumbers: true,
	themeSystem: 'bootstrap',
	events: {
		url: route.leaveevents,
		method: 'POST',
		extraParams: {
			_token: csrf,
		},
	},
	eventDidMount: function(info) {
		$(info.el).tooltip({ ...config.tooltip,
    title: info.event.extendedProps.description,
});
	},
	eventTimeFormat: { // like '14:30:00'
		hour: '2-digit',
		minute: '2-digit',
		// second: '2-digit',
		hour12: true
	}
});
calendar.render();
