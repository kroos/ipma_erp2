window.data = {
	route: {
		hcaldstart: '{{ route('hcaldstart') }}',
		hcaldend: '{{ route('hcaldend') }}',
	},
	url: {
		holidaycalendar: '{{ url('holidaycalendar') }}',
	},
	old: {
		holidaycalendar: @json(isset($holidaycalendar) ? $holidaycalendar->id : null),
	},
	errors: @json($errors->toArray()),
};
