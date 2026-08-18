window.data = {
	route: {
		customer: '{{ route('customer.customer') }}',
		getOptSalesType: "{{ route('sales.getOptSalesType') }}",
		getOptSalesDeliveryType: "{{ route('sales.getOptSalesDeliveryType') }}",
		uom: '{{ route('uom.uom') }}',
		machine: '{{ route('machine.machine') }}',
		machineaccessories: '{{ route('machineaccessories.machineaccessories') }}',
		getOptSalesGetItem: "{{ route('getOptSalesGetItem') }}",
	},
	url: {
		salesjobdescription: `{{ url('salesjobdescription') }}`,
	},
	old: {
		customerid: @json(old('customer_id', @$sale?->customer_id)),
		salestypeid: @json(old('sales_type_id', @$sale?->sales_type_id)),
		oldItemsValuec: @json($oldItemsValuec ?? []),
		specialrequest: @json(old('special_request', @$sale?->special_request)),
		salesJD: @json($salesJD ?? []),
	},
	errors: @json($errors->toArray()),
};

