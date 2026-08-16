const { route, url, old, errors } = window.data;

function getError(name) {
	return errors[name] ? errors[name][0] : null;
}

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* counting mc leave balance */
$(document).on('keyup mouseup', '#ala', function () {
	var balance = ((parseFloat($(this).val()) * 100) / 100) + (parseFloat(old.mc_leave_balance) || 0);
	$('#alb').val(balance);
});

/* validator */
$('#form').bootstrapValidator({
	fields: {
		mc_leave: {
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
		mc_leave_adjustment: {
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
		mc_leave_utilize: {
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
		mc_leave_balance: {
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
