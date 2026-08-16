const { route, url, old, errors, editId } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
$('#loc').select2({ ...config.select2,
});

/////////////////////////////////////////////////////////////////////////////////////////
//date
$('#from').datetimepicker({ ...config.datetimepicker,
})
.on("dp.change dp.show dp.update", function (e) {
	var minDate = $('#from').val();
	$('#to').datetimepicker('minDate', minDate);
	$('#form').bootstrapValidator('revalidateField', 'date_from');
});


$('#to').datetimepicker({ ...config.datetimepicker,
})
.on("dp.change dp.show dp.update", function (e) {
	var maxDate = $('#to').val();
	$('#from').datetimepicker('maxDate', maxDate);
	$('#form').bootstrapValidator('revalidateField', 'date_to');
});


/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator

var fields = {
	'date_from': {
		validators: {
			notEmpty: {
				message: 'Please insert date start. '
			},
			date: {
				format: 'YYYY-MM-DD',
				message: 'Please insert date start. '
			},
		}
	},
	'date_to': {
		validators: {
			notEmpty: {
				message: 'Please insert date end. '
			},
			date: {
				format: 'YYYY-MM-DD',
				message: 'Please insert date end. '
			},
		}
	},
};

if (!editId) {
	fields['staff_id[]'] = {
		validators: {
			notEmpty: {
				message: 'Please choose '
			},
		}
	};
}

$('#form').bootstrapValidator({
	fields: fields
});
