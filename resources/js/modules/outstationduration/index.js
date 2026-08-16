const { route, old } = window.data;

/////////////////////////////////////////////////////////////////////////
// fullcalendar
var calendarEl = document.getElementById('calendar');
var calendar = new Calendar(calendarEl, {
	aspectRatio: 1.0,
	height: 2000,
	plugins: [
		timeGridPlugin,
		dayGridPlugin,
		multiMonthPlugin,
		momentPlugin,
		bootstrap5Plugin
	],
	initialView: 'multiMonthYear',
	// initialView: 'dayGridMonth',
	headerToolbar: {
		left: 'prev,next today',
		center: 'title',
		right: 'multiMonthYear,dayGridMonth,timeGridWeek'
	},
	weekNumbers: true,
	themeSystem: 'bootstrap',
	events: {
		url: route.staffoutstationduration,
		method: 'POST',
		extraParams: {
			staff_id: old.staff_id,
		},
	},
	// failure: function() {
	// 	alert('There was an error while fetching leaves!');
	// },
	eventDidMount: function(info) {
		$(info.el).tooltip({ ...config.tooltip,
    title: info.event.extendedProps.description,
});
	},
	eventTimeFormat: { // like '14:30:00'
		hour: '2-digit',
		minute: '2-digit',
		second: '2-digit',
		hour12: true
	}
});
calendar.render();
