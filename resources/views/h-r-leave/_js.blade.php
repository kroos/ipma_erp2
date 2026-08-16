window.data = {
	route: {
		getHRLeave: '{{ route('getHRLeave') }}',
	},
	url: {
		'h-r-leave': '{{ url('h-r-leave') }}',
	},
	old: {
		column_name: @json(old('column_name', @$var->column_name)),
		option_id: @json(old('option_id', @$var->option_id)),
		description: @json(old('description', @$var->description)),
		date_column: @json(old('date_column', @$var->date_column)),
		content: @json(old('content', @$var->content)),
		is_active: @json(old('is_active', @$var->is_active)),
	},
	errors: @json($errors->toArray()),
	// getError helper for JS template literals
	// usage: ${window.data.getError('field_name.name')}
	getError: function(name) {
		return window.data.errors[name] ? window.data.errors[name][0] : null;
	},
};
