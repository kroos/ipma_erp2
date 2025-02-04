function fetchData(type) {
	var result;
	$.ajax({
			url: "{{ route('leavedate.unavailabledate') }}",
			type: "POST",
			data: {
					id: {{ \Auth::user()->belongstostaff->id }},
					type: type,
					_token: '{!! csrf_token() !!}',
			},
			dataType: 'json',
			async: false, // Synchronous call: Not recommended for production.
			success: function (response) {
					// If you need to process the response into an array,
					// you can do that here. Otherwise, just assign it directly.
					result = $.map(response, function(value, index) {
							return value;
					});
			},
			error: function (jqXHR, textStatus, errorThrown) {
					console.error(textStatus, errorThrown);
			}
	});
	return result;
}
// console.log(fetchData(1));