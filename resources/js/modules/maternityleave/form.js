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
		maternity_leave: {
			validators: {
				notEmpty: {
					message: 'Please insert number with/out decimal. ',
				},
				numeric: {
					separator: '.',
					message: 'Use DOT (.) as separator. '
				}
			}
		},
		maternity_leave_adjustment: {
			validators: {
				notEmpty: {
					message: 'Please insert number with/out decimal. ',
				},
				numeric: {
					separator: '.',
					message: 'Use DOT (.) as separator. '
				}
			}
		},
		maternity_leave_utilize: {
			validators: {
				notEmpty: {
					message: 'Please insert number with/out decimal. ',
				},
				numeric: {
					separator: '.',
					message: 'Use DOT (.) as separator. '
				}
			}
		},
		maternity_leave_balance: {
			validators: {
				notEmpty: {
					message: 'Please insert number with/out decimal. ',
				},
				numeric: {
					separator: '.',
					message: 'Use DOT (.) as separator. '
				}
			}
		},
		remarks: {
			validators: {
			}
		},
	}
});
