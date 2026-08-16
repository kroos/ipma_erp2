const { url } = window.data;

// tooltip on reason
$(document).ready(function () {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

// datatables
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('D MMM YYYY h:mm a');
$('#leaves').DataTable({ ...config.datatable,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "columnDefs": [{ type: 'date', 'targets': [5] }],
    "order": [[5, "desc"]],
})
	.on('length.dt page.dt order.dt search.dt', function (e, settings, len) {
		$(document).ready(function () {
			$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
		});
	});

$('#bapprover, #sapprover, #hodapprover, #dirapprover, #hrapprover').DataTable({ ...config.datatable,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "columnDefs": [{ type: 'date', 'targets': [5] }],
    "order": [[5, "desc"]],
})
	.on('length.dt page.dt order.dt search.dt', function (e, settings, len) {
		$(document).ready(function () {
			$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
		});
	});

// cancel leave
$(document).on('click', '.cancel_btn', function (e) {
	var ackID = $(this).data('id');
	SwalDelete(ackID);
	e.preventDefault();
});

function SwalDelete(ackID) {
	swal.fire({ ...config.swal,
    title: 'Cancel Leave',
    text: 'Are you sure to cancel this leave?',
    icon: 'info',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancel',
    confirmButtonText: 'Yes',
    preConfirm: function () {
			return new Promise(function (resolve) {
				$.ajax({
					url: `${url.leavecancel}/${ackID}`,
					type: 'PATCH',
					dataType: 'json',
					data: {
						id: ackID,
						cancel: 3,
					},
				})
					.done(function (response) {
						swal.fire('Accept', response.message, response.status)
							.then(function () {
								window.location.reload(true);
							});
					})
					.fail(function () {
						swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
					})
			});
		},
})
		.then((result) => {
			if (result.dismiss === swal.DismissReason.cancel) {
				swal.fire('Cancel Action', 'Leave is still active.', 'info')
			}
		});
}
//auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function (e) {
	window.location.reload(true);
});

// replacement approve leave
$(document).on('click', '.rapprover_btn', function (e) {
	var ackID = $(this).data('id');
	SwalDeleteR(ackID);
	e.preventDefault();
});

function SwalDeleteR(ackID) {
	swal.fire({ ...config.swal,
    title: 'Approve Leave',
    text: 'Are you sure to approve this leave?',
    icon: 'info',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancel',
    confirmButtonText: 'Yes',
    preConfirm: function () {
			return new Promise(function (resolve) {
				$.ajax({
					url: url.leaverapprove + '/' + ackID,
					type: 'PATCH',
					dataType: 'json',
					data: {
						id: ackID,
						cancel: 3,
					},
				})
					.done(function (response) {
						swal.fire('Accept', response.message, response.status)
							.then(function () {
								window.location.reload(true);
							});
					})
					.fail(function () {
						swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
					})
			});
		},
})
		.then((result) => {
			if (result.dismiss === swal.DismissReason.cancel) {
				swal.fire('Cancel Action', 'Leave is still active.', 'info')
			}
		});
}
//auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function (e) {
	window.location.reload(true);
});
