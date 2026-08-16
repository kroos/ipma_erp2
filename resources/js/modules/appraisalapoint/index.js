const { url } = window.data;

////////////////////////////////////////////////////////////////////////////////////
$('.form-select').select2({ ...config.select2,
});

$(document).on('click', '.form-button', function(e){
	var formid = $(this).data('id');

	$('#appraisal_category_id' + formid).select2({ ...config.select2,
    dropdownParent: $('#form' + formid),
});
});


////////////////////////////////////////////////////////////////////////////////////
// DELETE APOINT APPRAISAL
$(document).on('click', '.pivot_delete', function(e){
	var pivotId = $(this).data('id');
	SwalPivotDelete(pivotId);
	e.preventDefault();
});

function SwalPivotDelete(pivotId){
	swal.fire({ ...config.swal,
    title: 'DELETE',
    text: "Do you want to delete?",
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: 'DELETE',
					url: url.appraisalapoint + '/' + pivotId,
					data: {
						id: pivotId,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Deleted', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Error', 'Something wrong with ajax!', 'error');
				})
			});
		},
})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Delete has been cancelled', 'info')
		}
	});
}


////////////////////////////////////////////////////////////////////////////////////
// UPDATE APPRAISAL CATEGORY
$(".form-appraisal-category").on('submit', function (e) {
	var ids = $(this).data('id');

	e.preventDefault();
	$.ajax({
		url: url.appraisalapointUpdate,
		type: 'PATCH',
		data: {
			id: ids,
			category_id: $('#appraisal_category_id' + ids).val(),
		},
		dataType: 'json',
		global: false,
		async: false,
		success: function (response) {
			$('#form').modal('hide');
			// var row = $('#form').parent().parent();
			// row.remove();
			swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
				if (result.isConfirmed) {
					location.reload();
				}
			});
		},
		error: function (resp) {
			const res = resp.responseJSON;
			$('#form').modal('hide');
			swal.fire('Error!', res.message, 'error');
		}
	});
});


////////////////////////////////////////////////////////////////////////////////////
// DISTRIBUTE APPRAISAL
$(document).on('click', '.distribute', function(e){

	e.preventDefault();
	swal.fire({ ...config.swal,
    title: 'DISTRIBUTE',
    text: "Do you want to distribute current year appraisal?",
    icon: 'info',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: 'PATCH',
					url: url.appraisallistUpdate,
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Distributed', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Error', 'Something wrong with ajax!', 'error');
				})
			});
		},
})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Process has been cancelled', 'info')
		}
	});
});
