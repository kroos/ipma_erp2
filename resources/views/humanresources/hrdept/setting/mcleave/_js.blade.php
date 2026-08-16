window.data = {
	route: {
		generatemcleave: '{{ route('generatemcleave') }}',
	},
	url: {
	},
	old: {
		mc_leave_balance: @json(isset($mcleave) ? $mcleave->mc_leave_balance : 0),
	},
	errors: @json($errors->toArray()),
};
