window._ = require('lodash');
// window.Popper = require('../../node_modules/popper.js/src/index').default;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    require('bootstrap/dist/js/bootstrap.bundle');
    window.$ = window.jQuery = require('jquery/dist/jquery');
	require('@claviska/jquery-minicolors');
    require('@fortawesome/fontawesome-free');
	require('datatables.net');
    require('datatables.net-autofill');
    require('datatables.net-colreorder');
    require('datatables.net-fixedheader');
    require('datatables.net-responsive');
    require('datatables.net-bs5');
    require('datatables.net-autofill-bs5');
    require('datatables.net-colreorder-bs5');
    require('datatables.net-fixedheader-bs5');
    require('datatables.net-responsive-bs5');

    // these are different breed of javascript which previously compatible with jquery... hareyyyyy
    // require('fullcalendar');
    // require('chart.js');

    require('pc-bootstrap4-datetimepicker');
    // require('../../node_modules/jquery-chained/jquery.chained');
    // require('../../node_modules/jquery-chained/jquery.chained.remote');
    require('jquery-ui/dist/jquery-ui');
    require('./dataTable-any-number');
    require('./datetime-moment');

    require('select2');
	window.moment = require('moment');
    moment().format();

    const Swal = require('sweetalert2');
	window.swal = require ('sweetalert2');

	require ('./bootstrapValidator4/js/bootstrapValidator');
    require ('./bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });


