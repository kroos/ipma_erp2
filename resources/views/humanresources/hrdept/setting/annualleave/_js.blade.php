window.data = {
	route: {
		generateannualleave: '{{ route('generateannualleave') }}',
	},
	url: {
	},
	old: {
		annual_leave_balance: @json(isset($annualleave) ? $annualleave->annual_leave_balance : 0),
	},
	errors: @json($errors->toArray()),
};
