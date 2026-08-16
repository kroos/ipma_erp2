const { route, old, errors } = window.data;

if (old.eligible) {
	navigator.geolocation.getCurrentPosition(function (location) {
		console.log(location.coords.latitude);
		console.log(location.coords.longitude);
		console.log(location.coords.accuracy);
		$('#lat').val(location.coords.latitude);
		$('#lon').val(location.coords.longitude);
		$('#acc').val(location.coords.accuracy);
		var lat = location.coords.latitude;
		var lon = location.coords.longitude;

		// initializing google map
		let map;
		async function initMap() {
			const position = { lat: lat, lng: lon };
			const { Map } = await google.maps.importLibrary("maps");
			const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

			map = new Map(document.getElementById("map_canvas"), {
				zoom: 15,
				center: position,
				mapId: "DEMO_MAP_ID",
			});

			const marker = new AdvancedMarkerElement({
				map: map,
				position: position,
				title: "My Location",
			});
		}
		initMap();
	});
}

$(document).on('click', '.delete_button', function (e) {
	if (!route || !route.destroy) {
		return;
	}

	var outId = $(this).data('id');
	var $row = $(this).closest('tr');

	swal.fire({ ...config.swal,
    type: 'warning',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    preConfirm: function () {
			return new Promise(function (resolve) {
				$.ajax({
					url: route.destroy.replace(':id', outId),
					type: 'DELETE',
					data: {
					},
					dataType: 'json'
				})
				.done(function (response) {
					swal.fire('Deleted!', response.message, response.status)
					.then(function () {
						$row.remove();
					});
				})
				.fail(function () {
					swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
				});
			});
		},
})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Your data is safe from delete', 'info');
		}
	});

	e.preventDefault();
});
