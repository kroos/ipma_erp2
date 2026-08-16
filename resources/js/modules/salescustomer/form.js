const { route, url, old, errors } = window.data;

function getError(name) {
	return errors[name] ? errors[name][0] : null;
}

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* validator */
$('#form').bootstrapValidator({
	fields: {
		customer: {
			validators: {
				notEmpty: {
					message: 'Please insert customer name'
				},
			}
		},
	}
});
