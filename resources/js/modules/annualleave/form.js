const { route, url, old, errors } = window.data;

function getError(name) {
	return errors[name] ? errors[name][0] : null;
}

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* counting annual leave balance */
$(document).on('keyup mouseup', '#ala', function () {
	let adjustment = parseFloat($(this).val()) || 0;
	let currentBalance = parseFloat(old.annual_leave_balance) || 0;
	let newBalance = currentBalance + adjustment;
	$('#alb').val(newBalance.toFixed(1));
});

/* validator */
$('#form').bootstrapValidator({
	fields: {
		annual_leave: {
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
		annual_leave_adjustment: {
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
		annual_leave_utilize: {
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
		annual_leave_balance: {
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
