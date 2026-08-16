/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* bootstrap validator */
$('#form').bootstrapValidator({
	fields: {
		'customer': {
			validators: {
				notEmpty: {
					message: 'Please Insert Company Name.'
				},
			}
		},
		'contact': {
			validators: {
				notEmpty: {
					message: 'Please Insert Customer Name'
				},
			}
		},
		'phone': {
			validators: {
				regexp: {
					regexp: /^\d+$/,
					message: 'Please Insert a Valid Phone Number.'
				},
			}
		},
		'fax': {
			validators: {
				regexp: {
					regexp: /^\d+$/,
					message: 'Please Insert a Valid Fax Number.'
				},
			}
		},
	}
});