const { route, url, old } = window.data;

function loadTable(){
	$.post(route.getHRLeave, function (response) {
		table.clear().rows.add(response).draw();
	}, 'json');
}

const kursus = [
		{ data: 'id', title: 'ID', defaultContent: '-', className: 'text-center', },
		{
			data: 'id',
			title: '#',
			className: 'text-center',
			defaultContent: '-',
			orderable: false,
			searchable:false,
			render: function(data){
				return `
					<div class="btn-group btn-group-sm" role="group">
						<a href="${url["h-r-leave"]}/${data}" class="btn btn-sm btn-outline-info" title="View">
							<i class="fa-regular fa-eye"></i>
						</a>

						<a href="${url["h-r-leave"]}/${data}/edit" class="btn btn-sm btn-outline-info" title="Edit">
							<i class="fa-regular fa-pen-to-square"></i>
						</a>

						<button type="button" data-id="${data}" title="Delete" class="delete_button btn btn-sm btn-outline-danger">
							<i class="fas fa-trash fa-lg"></i>
						</button>
					</div>
				`;
			}
		}
	];

const dtConfig = {
	...config.datatable,
	lengthMenu: [ [10, 20, 30, 50, -1], [10, 20, 30, 50, -1] ],
	order: [[0, 'asc']/*, [1, 'asc']*/],
	data: [],
	columns: kursus
};

/* datatable */
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'YYYY' );
$.fn.dataTable.moment( 'h:mm a' );

const table = $('#h-r-leave').DataTable(dtConfig);

$(document).on('click', '.delete_button', function(e){
	e.preventDefault();
	SwalDelete($(this).data('id'));
});

function SwalDelete(itemId){
	swal.fire({
	...config.swal,
		preConfirm: () => {
			return $.ajax({
				url: `${url["h-r-leave"]}/${itemId}`,
				type: 'DELETE',
				data: { id: itemId },
				dataType: 'json'
			})
			.done(response => {
				swal.fire('Deleted!', response.message, response.status);
				loadTable();
			})
			.fail(() => {
				swal.fire(
					config.swal.errorTitle,
					config.swal.errorMessage,
					config.swal.errorType
				);
			});
		}
	});
}

loadTable();
