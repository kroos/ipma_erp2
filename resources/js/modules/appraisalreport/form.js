const { route, batch } = window.data;

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

// File upload via Ajax
$("#form").on('submit', function(e){
	e.preventDefault();
	$.ajax({
		type: 'POST',
		url: route.store,
		data: new FormData(this),
		contentType: false,
		cache: false,
		processData:false,
		beforeSend: function(){
			$(".progress-bar").width('0%');
			$('#uploadStatus').html('<i class="fa-solid fa-spinner fa-spin-pulse fa-beat-fade"></i>');
		},
		error: function(resp){
			const res = resp.responseJSON;
			swal.fire('Error!', res.message,'error')
			.then(function(){
				window.location.reload(true);
			});
		},
		success: function(jqXHR, resp, errorThrown){
			// console.log(jqXHR, resp, errorThrown);
			window.location.replace(jqXHR);					// redirect action
		}
	});
});
