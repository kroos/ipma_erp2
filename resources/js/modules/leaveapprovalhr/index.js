const { route, url, old, errors } = window.data;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* datatables */
var table = $('#leaveapproval-hr').DataTable({ ...config.datatable, processing: true, serverSide: false, ajax: { url: url.table, dataSrc: 'data' }, columns: [{data:'leave_no_link'},{data:'username'},{data:'name'},{data:'leave_type_code'},{data:'reason'},{data:'date_applied'},{data:'dts'},{data:'dte'},{data:'dper'},{data:'bapp'},{data:'supp'},{data:'hodd'},{data:'dirr'},{data:'approve', orderable:false }], columnDefs: [ { type: 'date', targets: [5,6,7] } ], order: [[6, 'desc']], paging: false, rowCallback: function(row, data){ if (data.DT_RowClass) $(row).addClass(data.DT_RowClass); }, initComplete: function(){ $('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip }); } });

/* approve-button handler (delegated) */
$(document).on('click', '.approve-btn', function (e) {
	e.preventDefault();
	var $btn = $(this);
	var id = $btn.data('id');
	var rowData = table.row($btn.closest('tr')).data();
	if (!rowData || !rowData.modal_html) return;
	var $modal = $(rowData.modal_html);
	$('.page-humanresources-hrdept-leave-' + window.data.type + 'leaveapproval-index').append($modal);
	var el = document.getElementById('hrapproval' + id);
	var modal = new bootstrap.Modal(el);
	modal.show();
	$modal.on('hidden.bs.modal', function () {
		$modal.remove();
	});
});

/* form submit handler (delegated) */
$(document).on('submit', '.form', async function (e) {
	e.preventDefault();
	var form = $(this);
	var ids = form.data('id');
	try {
		var response = await $.ajax({ url: route.patch, type: 'PATCH', data: { id: ids, leave_status_id: form.find('input[name="leave_status_id"]:checked').val(), verify_code: form.find('input[name="verify_code"]').val(), remarks: form.find('textarea[name="remarks"]').val() }, dataType: 'json' });
		$('#' + 'hr' + 'approval' + ids).modal('hide');
		swal.fire('Success!', response.message, 'success');
		table.ajax.reload();
	} catch (err) {
		var res = err.responseJSON ?? { message: 'Unknown error' };
		swal.fire('Error!', res.message, 'error');
	}
});