const { route, batch } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
$('#week1,#week2').select2({ ...config.select2,
    ajax: {
		url: route.weekdates,
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				search: params.term,
			}
			return query;
		}
	},
});

/////////////////////////////////////////////////////////////////////////////////////////
if (batch) {
	setInterval(percent, 500);

	function percent() {
		$.ajax({
			url: route.progress,
			type: "GET",
			data: { id: batch },
			dataType: 'json',
			success: function (response) {
				window.percentbar = response.progress;
				$('.progress').attr('aria-valuenow', percentbar).css('width', percentbar + '%');
				$(".csvprogress").width(percentbar + '%');
				$(".csvprogress").html(percentbar +'%');
				$('#processedJobs').html(response.processedJobs);
				console.log(percentbar);
				if (percentbar == 100) {
					clearInterval(percent);
					window.location.replace(route.create);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		})
	}
}
/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator
$('#form').bootstrapValidator({
	fields: {
		date_from: {
			validators: {
				notEmpty: {
					message: 'Please choose '
				},
			}
		},
		date_to: {
			validators: {
				notEmpty: {
					message: 'Please choose '
				},
			}
		},
	}
});
