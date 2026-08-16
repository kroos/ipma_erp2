window.data = {
	route: {
		yearworkinghourstart: '{{ route('yearworkinghourstart') }}',
		yearworkinghourend: '{{ route('yearworkinghourend') }}',
	},
	url: {
	},
	old: {
		workinghour: @json(isset($workinghour) ? $workinghour->id : null),
	},
	errors: @json($errors->toArray()),
};
