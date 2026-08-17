const { url } = window.data;

// date sorting for the DataTables columns
$.fn.dataTable.moment('D MMM YYYY');
$.fn.dataTable.moment('D MMM YYYY h:mm a');

function initTooltips() {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
}

$(document).ready(function () {
	initTooltips();

	$.getJSON(url.myLeaves)
		.done(function (res) {
			const data = res.data || { leaves: [], backups: [] };

			if (!data.leaves.length) {
				$('#no-leave-msg').show();
				$('#leaves').hide();
			} else {
				$('#leaves').DataTable({
					...config.datatable,
					data: data.leaves,
					"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					"columnDefs": [{ type: 'date', 'targets': [5] }],
					"order": [[5, "desc"]],
					"columns": [
						{ data: 'hr9' },
						{ data: 'applied' },
						{ data: 'code' },
						{ data: 'reason' },
						{ data: 'from' },
						{ data: 'to' },
						{ data: 'period' },
						{ data: 'verify' },
						{ data: 'approvals' },
						{ data: 'status' },
					],
				}).on('draw.dt', initTooltips);
			}

			if (data.backups.length) {
				$('#backup-approval-wrap').show();
				$('#bapprover').DataTable({
					...config.datatable,
					data: data.backups,
					"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					"columnDefs": [{ type: 'date', 'targets': [5] }],
					"order": [[5, "desc"]],
					"columns": [
						{ data: 'name' },
						{ data: 'code' },
						{ data: 'reason' },
						{ data: 'applied' },
						{ data: 'from' },
						{ data: 'to' },
						{ data: 'period' },
						{ data: 'status' },
					],
					"createdRow": function (row, data) {
						if (data.row_class) {
							$(row).addClass(data.row_class);
						}
					},
				}).on('draw.dt', initTooltips);
			}
		})
		.fail(function () {
			$('#leaves').hide();
			$('#no-leave-msg').show();
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

// auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function (e) {
	window.location.reload(true);
});
