const { route, url, old, errors } = window.data;
const csrf = $('meta[name=csrf-token]').attr('content');

/* select2 */
$('.form-select').select2({ ...config.select2,
});

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* datatables */
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#attendance').DataTable({ ...config.datatable,
    "searching": false,
    "info": false,
    "paging": false,
    "lengthMenu": [ [30, 60, 100, -1], [30, 60, 100, "All"] ],
    "columnDefs": [
		{ type: 'date', 'targets': [0] },
		{ type: 'time', 'targets': [2] },
		{ type: 'time', 'targets': [3] },
		{ type: 'time', 'targets': [4] },
		{ type: 'time', 'targets': [5] },
		{ type: 'time', 'targets': [6] },
	],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});
});

$('#al, #mc, #ml').DataTable({ ...config.datatable,
    "lengthMenu": [ [30, 60, 100, -1], [30, 60, 100, "All"] ],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});
});

/* fullcalendar cant use jquery */
var calendarEl = document.getElementById('calendar');
var calendar = new Calendar(calendarEl, {
	aspectRatio: 1.0,
	height: 2000,
	plugins: [
		timeGridPlugin,
		dayGridPlugin,
		multiMonthPlugin,
		momentPlugin,
		bootstrap5Plugin
	],
	initialView: 'multiMonthYear',
	// initialView: 'dayGridMonth',
	// multiMonthMaxColumns: 1,					// force a single column
	headerToolbar: {
		left: 'prev,next today',
		center: 'title',
		right: 'multiMonthYear,dayGridMonth,timeGridWeek'
	},
	weekNumbers: true,
	themeSystem: 'bootstrap',
	events: {
		url: route.staffattendance,
		method: 'POST',
		extraParams: {
			_token: csrf,
			staff_id: old.staffId,
		},
	},
	// failure: function() {
	// 	alert('There was an error while fetching leaves!');
	// },
	eventDidMount: function(info) {
		$(info.el).tooltip({ ...config.tooltip,
    title: info.event.extendedProps.description,
});
	},
	eventTimeFormat: { // like '14:30:00'
		hour: '2-digit',
		minute: '2-digit',
		second: '2-digit',
		hour12: true
	}
});
calendar.render();

/* attendance statistic chart */
$.ajax({
	url: route.staffpercentage,
	method: 'POST',
	dataType: 'json',
	data: {
		id: old.staffId,
	},
})
.done(function (data) {
	new Chart(document.getElementById('myChart'), {
		type: 'line',
		data: {
			labels: data.map(row => row.month),
			datasets: [
						{
							type: 'line',
							label: 'Attendance Percentage By Month(%)',
							data: data.map(row => row.percentage),
							tension: 0.3,
						},
						{
							type: 'bar',
							label: 'Leaves By Month',
							data: data.map(row => row.leaves)
						},
						{
							type: 'bar',
							label: 'Absents By Month',
							data: data.map(row => row.absents)
						},
						{
							type: 'bar',
							label: 'Working Days By Month (Person Available)',
							data: data.map(row => row.working_days)
						},
						{
							type: 'bar',
							label: 'Work Days By Month',
							data: data.map(row => row.workdays)
						},
			]
		},
		options: {
			responsive: true,
			scales: {
				y: {
					beginAtZero: true
				}
			},
			interaction: {
				intersect: false,
				mode: 'index',
			},
		},
		plugins: {
			legend: {
				position: 'top',
			},
			title: {
				display: true,
				text: 'Attendance Statistic'
			},
		},
	});
})
.fail(function (jqXHR, textStatus, errorThrown) {
	// console.log(textStatus, errorThrown);
});
