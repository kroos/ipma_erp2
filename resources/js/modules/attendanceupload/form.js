const { route, batch } = window.data;

if (batch) {
	setInterval(poll, 100);

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
			error: function () {
			}
		});
	}
}

$(document).on('submit', '#form', function (evnt) {
	if (!$('#softcopy').val()) {
		swal.fire('Error!', 'No file upload', 'error')
		.then(function () {
			window.location.reload(true);
		});
		return;
	}

	evnt.preventDefault();
	$.ajax({
		xhr: function () {
			const xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener('progress', function (evt) {
				if (evt.lengthComputable) {
					const percentComplete = (evt.loaded / evt.total) * 100;
					$('#progressBar').attr('aria-valuenow', percentComplete).css('width', percentComplete + '%');
					$('.percent_upload').width(percentComplete.toPrecision(4) + '%');
					$('.percent_upload').html(percentComplete.toPrecision(4) + '%');
				}
			}, false);
			return xhr;
		},
		type: 'POST',
		url: route.store,
		contentType: false,
		cache: false,
		processData: false,
		data: new FormData(this),
		beforeSend: function () {
			$('.progress-bar').width('0%');
			$('#uploadStatus').html('<i class="fa-solid fa-spinner fa-spin-pulse fa-beat-fade"></i>');
		},
		success: function (data) {
			window.location.replace(data);
		},
		error: function (resp) {
			const res = resp.responseJSON;
			swal.fire('Error!', res.message, 'error')
			.then(function () {
				window.location.reload(true);
			});
		},
	});
});

$('#softcopy').change(function () {
	const allowedTypes = ['application/vnd.ms-excel'];
	const file = this.files[0];
	const fileType = file.type;
	if (!allowedTypes.includes(fileType)) {
		swal.fire('Error!', 'Please select a valid file (CSV, XLS OR XLSX file/s only)', 'error')
		.then(function () {
			window.location.reload(true);
		});
		$('#softcopy').val('');
		return false;
	}
});
