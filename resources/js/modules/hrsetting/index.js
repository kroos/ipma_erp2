const { route, url, old, errors } = window.data;

/* datatables */
$('#setting').DataTable({ ...config.datatable,
    "paging": false,
    "order": [ 0, 'asc' ],
});

/* toggle switches (PATCH) */
$(document).on('change', '.setting-toggle', async function () {
	const $this = $(this);

	try {
		const response = await $.ajax({
			url: `${url.hrsetting}/${$this.data('id')}`,
			type: 'PATCH',
			data: {
				id: $this.data('id'),
				active: $this.prop('checked'),
			},
			dataType: 'json',
		});

		$(document.getElementById($this.attr('id')))
		.parent()
		.find('.form-check-label')
		.text(response.active);

		swal.fire('Good job!', response.status, 'success');
	} catch (err) {
		swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
	}
});

/* number input setting (PATCH) */
$(document).on('change', '.setting-number', async function () {
	const $this = $(this);

	try {
		const response = await $.ajax({
			url: `${url.hrsetting}/${$this.data('id')}`,
			type: 'PATCH',
			data: {
				id: $this.data('id'),
				active: $this.val(),
			},
			dataType: 'json',
		});

		swal.fire('Good job!', response.status, 'success');
	} catch (err) {
		swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
	}
});
