const { route, url, old } = window.data;

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

// tooltip on reason
$(document).ready(function () {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

// datatables - attendance
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('h:mm a');
$('#attendance').DataTable({ ...config.datatable,
    "searching": false,
    "info": false,
    "paging": false,
    "lengthMenu": [[30, 60, 100, -1], [30, 60, 100, "All"]],
    "columnDefs": [
		{ type: 'date', 'targets': [0] },
		{ type: 'time', 'targets': [2] },
		{ type: 'time', 'targets': [3] },
		{ type: 'time', 'targets': [4] },
		{ type: 'time', 'targets': [5] },
		{ type: 'time', 'targets': [6] },
	],
    "order": [[0, 'asc']],
})
	.on('length.dt page.dt order.dt search.dt', function (e, settings, len) {
		$(document).ready(function () {
			$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
		});
	});

// datatables - leave
$.fn.dataTable.moment('D MMM YYYY h:mm a');
$('#leave').DataTable({ ...config.datatable,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "columnDefs": [{ type: 'date', 'targets': [2, 3] }],
    "order": [[2, "desc"]],
})
	.on('length.dt page.dt order.dt search.dt', function (e, settings, len) {
		$(document).ready(function () {
			$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
		});
	});

$('#replacementleave').DataTable({ ...config.datatable,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "columnDefs": [{ type: 'date', 'targets': [0, 1] }],
})
	.on('length.dt page.dt order.dt search.dt', function (e, settings, len) {
		$(document).ready(function () {
			$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
		});
	});

$('#disc').DataTable({ ...config.datatable,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "columnDefs": [{ type: 'date', 'targets': [3] }],
    "order": [[3, "desc"]],
})
	.on('length.dt page.dt order.dt search.dt', function (e, settings, len) {
		$(document).ready(function () {
			$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
		});
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
