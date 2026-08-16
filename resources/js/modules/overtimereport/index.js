$('#date_start, #date_end').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});

$('#branch').select2({ ...config.select2,
});

$('#title').select2({ ...config.select2,
});

$('#month').select2({ ...config.select2,
});

$('#year').select2({ ...config.select2,
});

$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {
		date_start: {
			validators: {
				notEmpty: {
					message: 'Please select a start date.'
				}
			}
		},
		date_end: {
			validators: {
				notEmpty: {
					message: 'Please select a end date.'
				}
			}
		},
		branch: {
			validators: {
				notEmpty: {
					message: 'Please select a branch.'
				}
			}
		},
	}
});
