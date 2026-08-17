const { route, url, old, errors, isAdmin } = window.data;

/* tooltips (name cards, cell tooltips) */
function initTooltips() {
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
}

/* name column with the image + username tooltip card */
function nameTooltip(row) {
	var tip = '<div class="d-flex flex-column align-items-center text-center p-3 py-5">'
		+ '<img class="rounded-5 mt-3" width="180px" src="' + url.image + '/' + row.image + '">'
		+ '<span class="font-weight-bold">' + (row.name || '') + '</span>'
		+ '<span class="font-weight-bold">' + (row.username || '') + '</span>'
		+ '</div>';
	return '<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="' + tip.replace(/"/g, '&quot;') + '">' + (row.name || '') + '</span>';
}

var columns = [
	{ data: 'staff_id', visible: isAdmin, orderable: false, searchable: false },
	{ data: null, render: function (data, type, row, meta) { return meta.row + 1; }, orderable: false, searchable: false },
	{ data: 'username', render: function (data, type, row) { return '<a href="' + row.show_url + '" alt="Detail" title="Detail">' + (data || '') + '</a>'; } },
	{ data: 'name', render: function (data, type, row) { return nameTooltip(row); } },
	{ data: 'group' },
	{ data: 'nationality' },
	{ data: 'marital_status' },
	{ data: 'category' },
	{ data: 'department' },
	{ data: 'location' },
	{ data: 'leave_flow' },
	{ data: 'mobile' },
];

/* inactive rows: name opens the shared activate-ex-staff modal */
var inactiveColumns = columns.slice();
inactiveColumns[3] = {
	data: 'name',
	render: function (data, type, row) {
		return '<button type="button" class="btn btn-sm btn-outline-secondary activate-btn" data-id="' + row.id + '">' + (data || '') + '</button>';
	},
};

$('#staff').DataTable({
	...config.datatable,
	processing: true,
	serverSide: false,
	ajax: { url: url.table, dataSrc: 'data.active' },
	columns: columns,
	order: [],
	initComplete: initTooltips,
	drawCallback: initTooltips,
});

$('#inactivestaff').DataTable({
	...config.datatable,
	processing: true,
	serverSide: false,
	ajax: { url: url.table, dataSrc: 'data.inactive' },
	columns: inactiveColumns,
	order: [],
	initComplete: initTooltips,
	drawCallback: initTooltips,
});

/* activate ex-staff: point the shared modal form at the row's activate route */
$(document).on('click', '.activate-btn', function (e) {
	e.preventDefault();
	var id = $(this).data('id');
	$('#activateStaffModal form').attr('action', url.activate + '/' + id);
	var modalEl = document.getElementById('activateStaffModal');
	var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
	modal.show();
});
