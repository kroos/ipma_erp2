const { timeStartAm, timeEndAm, timeStartPm, timeEndPm } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER IN
$('#in').datetimepicker({ ...config.datetimepicker,
    format: 'HH:mm',
})
.on('dp.change dp.update', function(e) {

	var breakStr = timeEndAm;
	var breakEnd = timeStartPm;

	if ($('#in').val() > timeStartAm) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = timeStartAm;
	}

	if ($('#break').val() < timeEndAm) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = timeEndAm;
	}

	if ($('#resume').val() > timeStartPm) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = timeStartPm;
	}

	if ($('#out').val() < timeEndPm) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = timeEndPm;
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER BREAK
$('#break').datetimepicker({ ...config.datetimepicker,
    format: 'HH:mm',
})
.on('dp.change dp.update', function(e) {

	var breakStr = timeEndAm;
	var breakEnd = timeStartPm;

	if ($('#in').val() > timeStartAm) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = timeStartAm;
	}

	if ($('#break').val() < timeEndAm) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = timeEndAm;
	}

	if ($('#resume').val() > timeStartPm) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = timeStartPm;
	}

	if ($('#out').val() < timeEndPm) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = timeEndPm;
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER RESUME
$('#resume').datetimepicker({ ...config.datetimepicker,
    format: 'HH:mm',
})
.on('dp.change dp.update', function(e) {

	var breakStr = timeEndAm;
	var breakEnd = timeStartPm;

	if ($('#in').val() > timeStartAm) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = timeStartAm;
	}

	if ($('#break').val() < timeEndAm) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = timeEndAm;
	}

	if ($('#resume').val() > timeStartPm) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = timeStartPm;
	}

	if ($('#out').val() < timeEndPm) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = timeEndPm;
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER OUT
$('#out').datetimepicker({ ...config.datetimepicker,
    format: 'HH:mm',
})
.on('dp.change dp.update', function(e) {

	var breakStr = timeEndAm;
	var breakEnd = timeStartPm;

	if ($('#in').val() > timeStartAm) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = timeStartAm;
	}

	if ($('#break').val() < timeEndAm) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = timeEndAm;
	}

	if ($('#resume').val() > timeStartPm) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = timeStartPm;
	}

	if ($('#out').val() < timeEndPm) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = timeEndPm;
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER DURATION
$('#time_work_hour').datetimepicker({ ...config.datetimepicker,
    format: 'HH:mm',
});


/////////////////////////////////////////////////////////////////////////////////////////
// SELECTION
$('#leave_id,#attendance_type_id,#daytype_id').select2({ ...config.select2,
});


/////////////////////////////////////////////////////////////////////////////////////////
// VALIDATION
$(document).ready(function() {
	$('#form').bootstrapValidator({
		fields: {

			daytype_id: {
				validators: {
					notEmpty: {
						message: 'Please select a day type.'
					},
				}
			},

			in: {
				validators: {
					notEmpty: {
						message: 'Please insert in time.'
					},
				}
			},

			break: {
				validators: {
					notEmpty: {
						message: 'Please insert break time.'
					},
				}
			},

			resume: {
				validators: {
					notEmpty: {
						message: 'Please insert resume time.'
					},
				}
			},

			out: {
				validators: {
					notEmpty: {
						message: 'Please insert out time.'
					},
				}
			},

		}
	})
});
