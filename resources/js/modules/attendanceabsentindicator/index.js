// Attendance Absent Indicator — fullcalendar multi-month view
// window.data.eventsUrl is injected by the blade (route('attendanceabsentindicator'))
const { eventsUrl } = window.data;
const csrf = $('meta[name=csrf-token]').attr('content');

var calendarEl = document.getElementById('calendar');
var calendar = new Calendar(calendarEl, {
	aspectRatio: 1.0,
	height: 2000,
	plugins: [multiMonthPlugin],
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
		url: eventsUrl,
		method: 'POST',
		extraParams: {
			_token: csrf,
			// staff_id: '117',
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
		// second: '2-digit',
		hour12: true
	}
});
calendar.render();
