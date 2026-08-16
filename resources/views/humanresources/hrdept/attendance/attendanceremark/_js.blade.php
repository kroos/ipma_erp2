window.data = {
	route: {
		stafflookup: '{{ route('staffcrossbackup.staffcrossbackup') }}',
	},
	url: {
		attendanceremark: '{{ url('attendanceremark') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};