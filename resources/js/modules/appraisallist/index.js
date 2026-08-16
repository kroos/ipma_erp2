const { url } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#staff').DataTable({ ...config.datatable,
    "paging": false,
    "order": [ 0, 'asc' ],
});

$(function () {
	$('[data-toggle="tooltip"]').tooltip({ ...config.tooltip })
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
