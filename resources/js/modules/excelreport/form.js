const { route, batch } = window.data;

$('#from1').datetimepicker({ ...config.datetimepicker,
    maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
})
.on('dp.change dp.update', function () {
	$('#form').bootstrapValidator('revalidateField', 'from');
	$('#to1').datetimepicker('minDate', $('#from1').val());
});

$('#to1').datetimepicker({ ...config.datetimepicker,
    maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
})
.on('dp.change dp.update', function () {
	$('#form').bootstrapValidator('revalidateField', 'to');
	$('#from1').datetimepicker('maxDate', $('#to1').val());
});

$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: 'fas fa-light fa-check',
		invalid: 'fas fa-sharp fa-light fa-xmark',
		validating: 'fas fa-duotone fa-spinner-third'
	},
	fields: {
		from: {
			validators: {
				notEmpty: {
					message: 'Please insert date '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Invalid date '
				},
			}
		},
		to: {
			validators: {
				notEmpty: {
					message: 'Please insert date '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Invalid date '
				},
			}
		},
	}
});

if (batch) {
	setInterval(poll, 500);

	function poll() {
		$.ajax({
			url: route.progress,
			type: 'GET',
			data: {
				id: batch,
			},
			dataType: 'json',
			success: function (response) {
				const percentbar = response.progress;
				$('.progress').attr('aria-valuenow', percentbar).css('width', percentbar + '%');
				$('.csvprogress').width(percentbar + '%').html(percentbar + '%');
				$('#processedJobs').html(response.processedJobs);
				if (percentbar == 100) {
					clearInterval(poll);
					window.location.replace(route.create);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	}
}
