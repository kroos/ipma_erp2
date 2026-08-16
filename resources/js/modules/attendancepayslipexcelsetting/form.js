const { route, url, old, errors } = window.data;

$(document).on('change', 'input[name=value][data-id]', function () {
	const id = $(this).data('id');

	$.ajax({
		url: url.update + '/' + id,
		type: 'PATCH',
		data: {
			id,
			value: $(this).val(),
		},
		dataType: 'json',
		global: false,
		success: function (response) {
			swal.fire('Good job!', response.message, response.status);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			swal.fire('Ooopss! Something wrong!', errorThrown, textStatus);
		}
	});
});
