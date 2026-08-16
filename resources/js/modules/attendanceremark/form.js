const { route, url, old, errors } = window.data;

function getError(name) {
	return errors[name] ? errors[name][0] : null;
}

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* select2 */
$('#staff').select2({ ...config.select2,
    ajax: {
		url: route.stafflookup,
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var data = {
				search: params.term,
				type: 'public'
			}
			return data;
		}
	},
});

/* date */
$('#from').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
})
.on("dp.change dp.show dp.update", function (e) {
	$('#to').datetimepicker('minDate', $('#from').val());
	$('#form').bootstrapValidator('revalidateField', 'date_from');
});

$('#to').datetimepicker({ ...config.datetimepicker,
})
.on("dp.change dp.show dp.update", function (e) {
	$('#from').datetimepicker('maxDate', $('#to').val());
	$('#form').bootstrapValidator('revalidateField', 'date_to');
});

/* bootstrap validator */
$('#form').bootstrapValidator({
	fields: {
		'staff_id': {
			validators: {
				notEmpty: {
					message: 'Please choose '
				},
			}
		},
		'date_from': {
			validators: {
				notEmpty: {
					message: 'Please insert date from. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date from. '
				},
			}
		},
		'date_to': {
			validators: {
				notEmpty: {
					message: 'Please insert date to. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date to. '
				},
			}
		},
		'attendance_remarks': {
			validators: {
				notEmpty: {
					message: 'Attendance remarks required. '
				},
			}
		},
		'hr_attendance_remarks': {
			validators: {
				notEmpty: {
					message: 'HR attendance remarks required. '
				},
			}
		},
		'remarks': {
			validators: {
				// remarks is nullable, so no validation needed unless we want to make it required
			}
		}
	}
});