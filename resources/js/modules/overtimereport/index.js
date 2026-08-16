var $ds = $('#date_start');
    var $de = $('#date_end');
    $ds.datetimepicker({ ...config.datetimepicker, useCurrent: true }).on('dp.change', function () {
        var v = $ds.val();
        if (v) {
            $de.datetimepicker('minDate', v);
        } else {
            $de.datetimepicker('minDate', false);
        }
    });
    $de.datetimepicker({ ...config.datetimepicker, useCurrent: true }).on('dp.change', function () {
        var v = $de.val();
        if (v) {
            $ds.datetimepicker('maxDate', v);
        } else {
            $ds.datetimepicker('maxDate', false);
        }
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
	...config.bootstrapValidator,
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
