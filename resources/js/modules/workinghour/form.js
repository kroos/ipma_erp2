const { route, url, old, errors } = window.data;

function getError(name) {
	return errors[name] ? errors[name][0] : null;
}

const isEdit = !!old.workinghour;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* date range (create + edit) */
$('#effective_date_start').datetimepicker({ ...config.datetimepicker,
})
.on("dp.change dp.show dp.update", function (e) {
	var minDate = $('#effective_date_start').val();
	$('#effective_date_end').datetimepicker('minDate', minDate);
	$('#form').bootstrapValidator('revalidateField', 'effective_date_start');
});

$('#effective_date_end').datetimepicker({ ...config.datetimepicker,
})
.on("dp.change dp.show dp.update", function (e) {
	var maxDate = $('#effective_date_end').val();
	$('#effective_date_start').datetimepicker('maxDate', maxDate);
	$('#form').bootstrapValidator('revalidateField', 'effective_date_end');
});

/* time pickers (edit only) */
$('#tsa, #tea, #tsp, #tep').datetimepicker({ ...config.datetimepicker,
    format: 'h:mm A',
})
.on('dp.change dp.show dp.update', function(){
	$('#form').bootstrapValidator('revalidateField', $(this).attr('name'));
});

/* validator */
var fields = {
	'effective_date_start': {
		validators: {
			notEmpty: {
				message: 'Please insert ramadhan date start. '
			},
			date: {
				format: 'YYYY-MM-DD',
				message: 'Please insert ramadhan date start. '
			},
		}
	},
	'effective_date_end': {
		validators: {
			notEmpty: {
				message: 'Please insert ramadhan date end. '
			},
			date: {
				format: 'YYYY-MM-DD',
				message: 'Please insert ramadhan date end. '
			},
		}
	},
};

/* remote year-existence check only on create */
if (!isEdit) {
	fields['effective_date_start'].validators.remote = {
		type: 'POST',
		url: route.yearworkinghourstart,
		message: 'The duration of Ramadhan month for this year is already exist. Please choose another year',
		data: function(validator) {
			return {
				effective_date_start: $('#effective_date_start').val(),
			};
		},
		delay: 1,
	};

	fields['effective_date_end'].validators.remote = {
		type: 'POST',
		url: route.yearworkinghourend,
		message: 'The duration of Ramadhan month for this year is already exist. Please choose another year',
		data: function(validator) {
			return {
				effective_date_end: $('#effective_date_end').val(),
			};
		},
	};
}

/* time fields only on edit */
if (isEdit) {
	var timeValidators = {
		notEmpty: {
			message: 'Please insert time',
		},
		regexp: {
			regexp: /^([1-5]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
			message: 'The value is not a valid time',
		}
	};

	fields['time_start_am'] = { validators: timeValidators };
	fields['time_end_am'] = { validators: timeValidators };
	fields['time_start_pm'] = { validators: timeValidators };
	fields['time_end_pm'] = {
		validators: {
			notEmpty: {
				message: 'Please insert time',
			},
			regexp: {
				regexp: /^([1-6]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
				message: 'The value is not a valid time',
			}
		}
	};
}

$('#form').bootstrapValidator({
	fields: fields
});
