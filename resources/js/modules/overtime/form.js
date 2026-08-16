const { route, url, old, errors, editId, staffId, hasMinDate } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
if (editId) {
	$('#mar, #rel').select2({ ...config.select2,
});
} else {
	$('#rel').select2({ ...config.select2,
    ajax: {
			url: route.samelocationstaff,
			type: 'POST',
			dataType: 'json',
			data: function (params) {
				var query = {
					id: staffId,
					search: params.term,
				}
				return query;
			}
		},
});

	$('#mar').select2({ ...config.select2,
    ajax: {
			url: route.overtimerange,
			type: 'POST',
			dataType: 'json',
			data: function (params) {
				var query = {
					id: staffId,
					search: params.term,
				}
				return query;
			}
		},
});
}

/////////////////////////////////////////////////////////////////////////////////////////
var pickerOptions = {
	icons: {
		time: "fas fas-regular fa-clock fa-beat",
		date: "fas fas-regular fa-calendar fa-beat",
		up: "fa-regular fa-circle-up fa-beat",
		down: "fa-regular fa-circle-down fa-beat",
		previous: 'fas fas-regular fa-arrow-left fa-beat',
		next: 'fas fas-regular fa-arrow-right fa-beat',
		today: 'fas fas-regular fa-calenday-day fa-beat',
		clear: 'fas fas-regular fa-broom-wide fa-beat',
		close: 'fas fas-regular fa-rectangle-xmark fa-beat'
	},
	format: 'YYYY-MM-DD',
	useCurrent: true,
};

if (editId) {
	$('#nam').datetimepicker(pickerOptions);
} else {
	if (hasMinDate) {
		pickerOptions.minDate = moment().format();
	}
	$('#nam').datetimepicker(pickerOptions).on("dp.change", function (e) {
		$('#form').bootstrapValidator('revalidateField', 'ot_date');
	});
}

/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator
if (editId) {
	$('#form').bootstrapValidator({
		fields: {
			ot_date: {
				validators: {
					notEmpty: {
						message: 'Please insert password. '
					},
				}
			},
			staff_id: {
				validators: {
					notEmpty: {
						message: 'Please choose. '
					},
				}
			},
			overtime_range_id: {
				validators: {
					notEmpty: {
						message: 'Please choose. '
					},
				}
			},
		}
	});
} else {
	$('#form').bootstrapValidator({
		fields: {
			ot_date: {
				validators: {
					notEmpty: {
						message: 'Please insert date. '
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'The value is not a valid date ',
					},
				}
			},
			staff_id: {
				validators: {
					notEmpty: {
						message: 'Please choose. '
					},
				}
			},
			overtime_range_id: {
				validators: {
					notEmpty: {
						message: 'Please choose. '
					},
				}
			},
		}
	});
}
