// import multiMonthPlugin from '@fullcalendar/multimonth';
// import dayGridPlugin from '@fullcalendar/daygrid';
// import timeGridPlugin from '@fullcalendar/timegrid';
// import listPlugin from '@fullcalendar/list';
// import momentPlugin from '@fullcalendar/moment';
// import bootstrap5Plugin from '@fullcalendar/bootstrap5';

const config = {
	select2: {
		theme: 'bootstrap-5',
		placeholder: 'Please choose',
		allowClear: true,
		closeOnSelect: true,
		width: '100%',
	},

	bootstrapValidator: {
		feedbackIcons: {
			valid: 'fas fa-light fa-check',
			invalid: 'fas fa-sharp fa-light fa-xmark',
			validating: 'fas fa-duotone fa-spinner-third'
		}
	},

	datetimepicker: {
		icons: {
			time: "fas fa-regular fa-clock fa-beat",
			date: "fas fa-regular fa-calendar fa-beat",
			up: "fa-regular fa-circle-up fa-beat",
			down: "fa-regular fa-circle-down fa-beat",
			previous: 'fas fa-regular fa-arrow-left fa-beat',
			next: 'fas fa-regular fa-arrow-right fa-beat',
			today: 'fas fa-regular fa-calendar-day fa-beat',
			clear: 'fas fa-regular fa-broom-wide fa-beat',
			close: 'fas fa-regular fa-rectangle-xmark fa-beat'
		},
		format: 'YYYY-MM-DD',
		useCurrent: false,
	},

	datatable: {
		lengthMenu: [[50, 100, -1], [50, 100, 'All']],
		columnDefs: [
			{ type: 'date', targets: [2] },
		],
		order: [[0, 'desc']],
		responsive: true,
		autoWidth: false,
		fixedHeader: true,
		dom: 'Bfrtip',
	},

	fullcalendar: {
		plugins: [
			multiMonthPlugin,
			dayGridPlugin,
			timeGridPlugin,
			listPlugin,
			momentPlugin,
			bootstrap5Plugin,
		],
		aspectRatio: 1.3,
		height: 2000,
		weekNumbers: true,
		titleFormat: 'D MMMM, YYYY',
		themeSystem: 'bootstrap5',
		initialView: 'multiMonthYear',
		headerToolbar: {
			left: 'prev,next today',
			center: 'title',
			right: 'multiMonthYear,dayGridMonth,timeGridWeek',
		},
	},
	tooltip: {
		placement: 'top',
		trigger: 'hover focus',
		container: 'body',
	},

	swal: {
		title: 'Are you sure?',
		text: 'It will be deleted permanently!',
		icon: 'warning',
		showCancelButton: true,
		allowOutsideClick: false,
		showLoaderOnConfirm: true,
		confirmButtonText: 'Yes, delete it!',
		/*confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',*/

		cancelTitle: 'Cancelled',
		cancelMessage: 'Your data is safe from delete',
		cancelType: 'info',

		errorTitle: 'Ajax Error',
		errorMessage: 'Something went wrong with ajax',
		errorType: 'error'
	},
};
export default config;

/*
import config from '@/config/plugins';

$('.series').select2(Config.select2);

$('#table').DataTable({
    ...Config.datatable,
    paging: false, // override example
});

new Calendar(el, {
    ...Config.fullcalendar,
});
*/
