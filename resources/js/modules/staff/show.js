const { route, url, old, errors } = window.data;

/* tooltips (re-applied after every DataTable draw) */
function initTooltips() {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
}

// scroll restore
if (typeof (Storage) !== 'undefined') {
	window.addEventListener('beforeunload', function () {
		sessionStorage.setItem('scrollPosition', window.scrollY);
	});

	window.addEventListener('load', function () {
		var scrollPosition = sessionStorage.getItem('scrollPosition');
		if (scrollPosition !== null) {
			window.scrollTo(0, scrollPosition);
			sessionStorage.removeItem('scrollPosition');
		}
	});
}

// select2
$('.form-select').select2(config.select2);

// deactivate staff
$(document).on('click', '.deactivate', function (e) {
	var staffId = $(this).data('id');
	DeactivateStaff(staffId);
	e.preventDefault();
});

function DeactivateStaff(staffId) {
	swal.fire({ ...config.swal,
    text: 'Please take note, this action will deactivate ' + old.staffName + '.',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, deactivate',
    preConfirm: function () {
			return new Promise(function (resolve) {
				$.ajax({
					type: 'PATCH',
					url: route.deactivatestaff + '/' + staffId,
					data: {
						id: staffId,
					},
					dataType: 'json'
				})
					.done(function (response) {
						swal.fire('Deleted!', response.message, response.status)
							.then(function () {
								window.location.reload(true);
							});
						window.location.replace(route.staffindex);
					})
					.fail(function () {
						swal.fire('Oops...', 'Something went wrong with system! Please try again later', 'error');
					})
			});
		},
})
		.then((result) => {
			if (result.dismiss === swal.DismissReason.cancel) {
				swal.fire('Cancelled', 'Your ' + old.staffName + ' is safe from deactivate', 'info')
			}
		});
}

// datatables - attendance (API, client-side processing)
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('h:mm a');
var attendanceTable = $('#attendance').DataTable({
	...config.datatable,
	processing: true,
	serverSide: false,
	ajax: {
		url: url.attendance,
		dataSrc: 'data',
		data: function (d) {
			d.year = $('#year').val();
			d.month = $('#month').val();
		},
	},
	columns: [
		{ data: 'date', type: 'date' },
		{ data: 'daytype' },
		{ data: 'in', type: 'time' },
		{ data: 'break', type: 'time' },
		{ data: 'resume', type: 'time' },
		{ data: 'out', type: 'time' },
		{ data: 'work_hour', type: 'time' },
		{ data: 'overtime' },
		{ data: 'leave_form' },
		{ data: 'leave_type' },
		{ data: 'remark' },
		{ data: 'outstation' },
	],
	searching: false,
	info: false,
	paging: false,
	lengthMenu: [[30, 60, 100, -1], [30, 60, 100, 'All']],
	order: [[0, 'asc']],
	initComplete: initTooltips,
	drawCallback: initTooltips,
});

// attendance year/month filter -> reload the table via the API
$('#attendanceForm').on('submit', function (e) {
	e.preventDefault();
	attendanceTable.ajax.reload();
});

// datatables - leave (API, client-side processing)
$.fn.dataTable.moment('D MMM YYYY h:mm a');
var leaveTable = $('#leave').DataTable({
	...config.datatable,
	processing: true,
	serverSide: false,
	ajax: { url: url.leaves, dataSrc: 'data' },
	columns: [
		{ data: 'no' },
		{ data: 'type' },
		{ data: 'applied', type: 'date' },
		{ data: 'from', type: 'date' },
		{ data: 'to', type: 'date' },
		{ data: 'duration' },
		{ data: 'reason' },
		{ data: 'status' },
		{ data: null, orderable: false, render: function (data, type, row) {
			return '<a href="' + row.show_url + '" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-eye"></i></a>';
		} },
	],
	lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
	order: [[2, 'desc']],
	initComplete: initTooltips,
	drawCallback: initTooltips,
});

// nested replacement-leave sub-table (row.leaves + row.total_days)
function replacementNested(data, row) {
	if (!data || !data.length) return '';
	var html = '<table class="table table-hover table-sm"><thead><tr><th>Leave ID</th><th>Duration</th></tr></thead><tbody>';
	data.forEach(function (l) {
		html += '<tr><td><a href="' + l.show_url + '" target="_blank">' + l.no + '</a></td><td>' + l.period_day + ' day/s</td></tr>';
	});
	html += '</tbody><tfoot><tr><th>Total</th><th>' + row.total_days + ' day/s</th></tr></tfoot></table>';
	return html;
}

// datatables - replacement leave (API, client-side processing)
var replacementTable = $('#replacementleave').DataTable({
	...config.datatable,
	processing: true,
	serverSide: false,
	ajax: { url: url.replacement, dataSrc: 'data' },
	columns: [
		{ data: 'from', type: 'date' },
		{ data: 'to', type: 'date' },
		{ data: 'location' },
		{ data: 'reason' },
		{ data: 'leave_total' },
		{ data: 'leave_utilize' },
		{ data: 'leave_balance' },
		{ data: 'leaves', render: replacementNested },
		{ data: null, orderable: false, render: function (data, type, row) {
			return '<a href="' + row.edit_url + '" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>';
		} },
	],
	lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
	order: [],
	initComplete: initTooltips,
	drawCallback: initTooltips,
});

// datatables - discipline (API, client-side processing)
var discTable = $('#disc').DataTable({
	...config.datatable,
	processing: true,
	serverSide: false,
	ajax: { url: url.disciplinaries, dataSrc: 'data' },
	columns: [
		{ data: 'action' },
		{ data: 'violation' },
		{ data: 'reason' },
		{ data: 'date', type: 'date' },
		{ data: 'softcopy_url', orderable: false, render: function (data, type, row) {
			return data ? '<a href="' + data + '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-text" style="font-size: 15px;"></i></a>' : '';
		} },
		{ data: null, orderable: false, render: function (data, type, row) {
			return '<a href="' + row.edit_url + '" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>'
				+ '&nbsp;<button type="button" class="btn btn-sm btn-outline-secondary delete_discipline" data-id="' + row.id + '" data-softcopy="' + row.softcopy + '" data-table="discipline"><i class="fa-regular fa-trash-can"></i></button>';
		} },
	],
	lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
	order: [[3, 'desc']],
	initComplete: initTooltips,
	drawCallback: initTooltips,
});

// delete discipline
$(document).on('click', '.delete_discipline', function (e) {
	var ackID = $(this).data('id');
	var ackSoftcopy = $(this).data('softcopy');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackSoftcopy, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackSoftcopy, ackTable) {
	swal.fire({ ...config.swal,
    title: 'Delete Discipline',
    text: 'Are you sure to delete this discipline?',
    icon: 'info',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancel',
    confirmButtonText: 'Yes',
    preConfirm: function () {
			return new Promise(function (resolve) {
				$.ajax({
					url: route.discipline + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						softcopy: ackSoftcopy,
						table: ackTable,
					},
				})
					.done(function (response) {
						swal.fire('Accept', response.message, response.status)
							.then(function () {
								window.location.reload(true);
							});
					})
					.fail(function () {
						swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
					})
			});
		},
})
		.then((result) => {
			if (result.dismiss === swal.DismissReason.cancel) {
				swal.fire('Cancel Action', '', 'info')
			}
		});
}

// fullcalendar
var calendarEl = document.getElementById('calendar');
var calendar = new Calendar(calendarEl, {
	...config.fullcalendar,
	aspectRatio: 1.0,
	themeSystem: 'bootstrap',
	events: {
		url: route.staffattendance,
		method: 'POST',
		extraParams: {
			_token: $('meta[name=csrf-token]').attr('content'),
			staff_id: old.staffId,
		},
	},
	eventDidMount: function (info) {
		$(info.el).tooltip({ ...config.tooltip,
    title: info.event.extendedProps.description,
});
	},
	eventTimeFormat: {
		hour: '2-digit',
		minute: '2-digit',
		hour12: true
	}
});
calendar.render();

// chart - attendance statistic
$.ajax({
	url: route.staffpercentage,
	type: 'POST',
	data: {
		id: old.staffId,
	},
	dataType: 'json',
	success: function (data) {
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
	},
});
