const { route, url, old, errors } = window.data;

const isEdit = !!old.staff_id;

/////////////////////////////////////////////////////////////////////////////////////////
//date
if (!isEdit) {
	$('#date').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
})
	.on('dp.change dp.update', function(e) {
		// console.log(e);

		//enable select 2 for backup
		$('#loc').select2({ ...config.select2,
    ajax: {
				url: route.outstationattendancelocation,
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						date_attend: $('#date').val(),
						search: params.term,
						type: 'public'
					}
					return query;
				}
			},
});

		// get staff
		$('#loc').on('change, select2:select', function (e) {
			// console.log($('#loc').val());

			$('#staff').select2({ ...config.select2,
    ajax: {
					url: route.outstationattendancestaff,
					type: 'POST',
					dataType: 'json',
					data: function (params) {
						var query = {
							outstation_id: $('#loc').val(),
							date_attend: $('#date').val(),
							search: params.term,
						}
						return query;
					}
				},
});
		});
	});
} else {
	$('#date1').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});

	$('#loc1').select2({ ...config.select2,
    ajax: {
			url: route.outstationattendancelocation,
			type: 'POST',
			dataType: 'json',
			data: function (params) {
				var query = {
					date_attend: $('#date').val(),
					search: params.term,
					type: 'public'
				}
				return query;
			},
			transport: function (params, success, failure) {
				var $request = $.ajax(params);

				$request.then(success);
				$request.fail(failure);
				console.log($request);
				// return $request;
			},
		},
});


	// Fetch the preselected item, and add to the control
	var location = $('#loc');
	$.ajax({
		url: route.outstationattendancelocation,
		type: "POST",
		data: {
				// id: $('#id').val(),
				date_attend: $('#date').val(),
		},
		dataType: 'json',
		global: false,
		async:false,
		done: (function(response) {
			// you will get response from your php page (what you echo or print)
			console.log(response);
			return response;
		}),
		fail: (function(jqXHR, textStatus, errorThrown) {
			alert( "error" );
			console.log(textStatus, errorThrown);
		}),
		always: (function() {
			// alert( "complete" );
		})
//	})
//	.then(function (data) {
//		console.log(data.results);
//		// create the option and append to Select2
//		var option = new Option(data.text, data.id, true, true);
//		location.append(option).trigger('change');

//		// manually trigger the `select2:select` event
//		location.trigger({
//			type: 'select2:select',
//			params: {
//				data: data
//			}
//		});
	});

	$('#staff1').select2({ ...config.select2,
    ajax: {
			url: route.outstationattendancestaff,
			type: 'POST',
			dataType: 'json',
			data: function (params) {
				var query = {
					outstation_id: $('#loc').val(),
					date_attend: $('#date').val(),
					search: params.term,
				}
				return query;
			}
		},
});
	$('#staff1').val(old.staff_id).trigger('change');
}

/////////////////////////////////////////////////////////////////////////////////////////
$('#in, #out').datetimepicker({ ...config.datetimepicker,
    format: 'h:mm A',
});

/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator

$('#form').bootstrapValidator({
	fields: {
		'staff_id[]': {
			validators: {
				notEmpty: {
					message: 'Please choose '
				},
			}
		},
		'date_attend': {
			validators: {
				notEmpty: {
					message: 'Please insert date. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date. '
				},
			}
		},
		'outstation_id': {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
	}
});
