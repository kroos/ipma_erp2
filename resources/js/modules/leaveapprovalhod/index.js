const { route, url, old, errors } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* form submit via ajax */
$(document).on('submit', '.form', async function (e) {
	e.preventDefault();

	let form = $(this);
	let ids  = form.data('id');

	try {
		const response = await $.ajax({
			url: form.attr('action'),
			type: 'PATCH',
			data: {
				id: form.find('input[name="id"]').val(),
				leave_status_id: form.find('input[name="leave_status_id"]:checked').val(),
				verify_code: form.find('#hodcode' + ids).val(),
				remarks: form.find('#remarks' + ids).val(),
			},
			dataType: 'json',
		});

		$('#hodapproval' + ids).modal('hide');

		/* remove row */
		form.closest('tr').remove();

		swal.fire('Success!', response.message, 'success');
	} catch (err) {
		let res = err.responseJSON ?? { message: 'Unknown error' };
		swal.fire('Error!', res.message, 'error');
	}
});

/* datatables */
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#bapprover, #sapprover, #hodapprover, #dirapprover, #hrapprover').DataTable({ ...config.datatable,
    paging: false,
    columnDefs: [ { type: 'date', targets: [5,6,7] } ],
    order: [[6, "desc" ]],
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
	});
});
