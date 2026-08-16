const { route, url, old, errors } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* form submit via ajax (direct binding) */
$(".form").on('submit', function(e){
	var ids = $(this).data('id');
	e.preventDefault();
	$.ajax({
		url: url.hrstatus,
		type: 'PATCH',
		data: {
			id: ids,
			leave_status_id: $(':input[name="leave_status_id"]:checked').val(),
			verify_code: $('#hrcode' + ids).val(),
			remarks: $('#remarks' + ids).val(),
		},
		dataType: 'json',
		global: false,
		async: false,
		success: function (response) {
			$('#hrapproval' + ids).modal('hide');
			var row = $('#hrapproval' + ids).parent().parent();
			row.remove();
			swal.fire('Success!', response.message, response.status);
		},
		error: function(resp) {
			const res = resp.responseJSON;
			$('#hrapproval' + ids).modal('hide');
			swal.fire('Error!', res.message, 'error');
		}
	});
});

/* form submit via ajax (delegated binding) */
$(document).on('submit', '.form', async function (e) {
	e.preventDefault();

	let form = $(this);
	let ids  = form.data('id');

	try {
		const response = await $.ajax({
			url: form.attr('action'),
			type: 'PATCH',
			data: {
				id: ids,
				leave_status_id: form.find('input[name="leave_status_id"]:checked').val(),
				verify_code: form.find('#hrcode' + ids).val(),
				remarks: form.find('#remarks' + ids).val(),
			},
			dataType: 'json',
		});

		$('#hrapproval' + ids).modal('hide');

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
