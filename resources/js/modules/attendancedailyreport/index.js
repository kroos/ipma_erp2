$('#date').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});

$('#form').bootstrapValidator({
	...config.bootstrapValidator,
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {
		date: {
			validators: {
				notEmpty: {
					message: 'Please select a date.'
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please select a valid date.'
				},
			}
		},
	}
});
