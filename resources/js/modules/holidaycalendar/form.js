const { route, url, old, errors } = window.data;

function getError(name) {
	return errors[name] ? errors[name][0] : null;
}

const isEdit = !!old.holidaycalendar;

/* tooltip */
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip({ ...config.tooltip });
});

/* date range */
$('#dstart').datetimepicker({ ...config.datetimepicker,
})
.on("dp.change dp.show dp.update", function (e) {
	var minDate = $('#dstart').val();
	$('#dend').datetimepicker('minDate', minDate);
	$('#form').bootstrapValidator('revalidateField', 'date_start');
});

$('#dend').datetimepicker({ ...config.datetimepicker,
})
.on("dp.change dp.show dp.update", function (e) {
	var maxDate = $('#dend').val();
	$('#dstart').datetimepicker('maxDate', maxDate);
	$('#form').bootstrapValidator('revalidateField', 'date_end');
});

/* validator */
var fields = {
	'date_start': {
		validators: {
			notEmpty: {
				message: 'Please insert holiday date start. '
			},
			date: {
				format: 'YYYY-MM-DD',
				message: 'Please insert holiday date start. '
			},
		}
	},
	'date_end': {
		validators: {
			notEmpty: {
				message: 'Please insert holiday date end. '
			},
			date: {
				format: 'YYYY-MM-DD',
				message: 'Please insert holiday date end. '
			},
		}
	},
	'holiday': {
		validators: {
			notEmpty: {
				message: 'Please insert the name of the holiday. '
			}
		}
	},
};

/* remote date-existence check only on create */
if (!isEdit) {
	fields['date_start'].validators.remote = {
		type: 'POST',
		url: route.hcaldstart,
		message: 'The date is already exist. Please choose another date. ',
		data: function(validator) {
			return {
				date_start: $('#dstart').val(),
			};
		},
	};

	fields['date_end'].validators.remote = {
		type: 'POST',
		url: route.hcaldend,
		message: 'The date is already exist. Please choose another date. ',
		data: function(validator) {
			return {
				date_end: $('#dend').val(),
			};
		},
		delay: 1,
	};
}

$('#form').bootstrapValidator({
	fields: fields
});
