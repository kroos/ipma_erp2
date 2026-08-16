const { url } = window.data;

////////////////////////////////////////////////////////////////////////////////////
$('#1_setting,#2_setting,#3_setting,#4_setting,#6_setting,#7_setting,#8_setting,#9_setting').change(function() {
	$.ajax({
		url: url.appraisalsettingupdate + '/' + $(this).data('id'),
		type: "PATCH",
		data : {
					id: $(this).data('id'),
					value1: $(this).val(),
				},
		dataType: 'json',
		global: false,
		async:false,
		success: function (response) {
			// console.log(response);
			swal.fire("Good job!", response.message, response.status);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// console.log(textStatus, errorThrown);
			swal.fire("Ooopss! Something wrong!", errorThrown, textStatus);
		}
	})
});

$('#22_setting,#32_setting').change(function() {
	$.ajax({
		url: url.appraisalsettingupdate + '/' + $(this).data('id'),
		type: "PATCH",
		data : {
					id: $(this).data('id'),
					value2: $(this).val(),
				},
		dataType: 'json',
		global: false,
		async:false,
		success: function (response) {
			// console.log(response);
			swal.fire("Good job!", response.message, response.status);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// console.log(textStatus, errorThrown);
			swal.fire("Ooopss! Something wrong!", errorThrown, textStatus);
		}
	})
});

$('#23_setting,#33_setting').change(function() {
	$.ajax({
		url: url.appraisalsettingupdate + '/' + $(this).data('id'),
		type: "PATCH",
		data : {
					id: $(this).data('id'),
					value3: $(this).val(),
				},
		dataType: 'json',
		global: false,
		async:false,
		success: function (response) {
			// console.log(response);
			swal.fire("Good job!", response.message, response.status);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// console.log(textStatus, errorThrown);
			swal.fire("Ooopss! Something wrong!", errorThrown, textStatus);
		}
	})
});
