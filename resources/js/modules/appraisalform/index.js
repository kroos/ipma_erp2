const { url } = window.data;

////////////////////////////////////////////////////////////////////////////////////
// DUPLICATE APPRAISAL
$(document).on('click', '.appraisal_duplicate', function(e){
	var appraisalId = $(this).data('id');
	SwalAppraisalDuplicate(appraisalId);
	e.preventDefault();
});

function SwalAppraisalDuplicate(appraisalId){
	swal.fire({ ...config.swal,
    title: 'DUPLICATE',
    text: "Do you want to duplicate the appraisal?",
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: 'GET',
					url: url.appraisalformduplicatestore,
					data: {
						id: appraisalId,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Duplicated', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
				})
			});
		},
})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Duplicate has been cancelled', 'info')
		}
	});
}


////////////////////////////////////////////////////////////////////////////////////
// DELETE APPRAISAL
$(document).on('click', '.appraisal_delete', function(e){
	var appraisalId = $(this).data('id');
	SwalAppraisalDelete(appraisalId);
	e.preventDefault();
});

function SwalAppraisalDelete(appraisalId){
	swal.fire({ ...config.swal,
    title: 'DELETE',
    text: "Do you want to deletet the appraisal?",
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes',
    preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: 'DELETE',
					url: url.appraisalform + '/' + appraisalId,
					data: {
						id: appraisalId,
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
					swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
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
