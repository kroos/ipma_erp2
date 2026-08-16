const { route } = window.data;

function renderCheckboxes() {
	const from = $('#from').val();
	const to = $('#to').val();

	if (!from || !to) {
		return;
	}

	if ($('.wrap_checkbox').children().length !== 0) {
		return;
	}

	const loadStaff = $.ajax({
		url: route.staffattendancelist,
		type: 'POST',
		data: {
			from,
			to,
		},
		dataType: 'json',
		global: false,
	});

	const loadBranch = $.ajax({
		url: route.branchattendancelist,
		type: 'POST',
		data: {
		},
		dataType: 'json',
		global: false,
	});

	$.when(loadStaff, loadBranch).done(function (staffRes, branchRes) {
		const staffs = staffRes[0];
		const branches = branchRes[0];

		$('.wrap_checkbox').append(
			'<div class="form-check form-check-inline mb-1 g-3 remove_checkbox">' +
				'<input class="form-check-input" type="checkbox" value="" id="checkAll">' +
				'<label class="form-check-label" for="checkAll">Name</label>' +
			'</div>'
		);

		$.each(branches, function () {
			const branchId = this.id;
			$('.wrap_checkbox').append(
				'<div class="form-check form-check-inline mb-1 g-3 remove_checkbox">' +
					'<input class="form-check-input" type="checkbox" value="" id="branch_' + branchId + '">' +
					'<label class="form-check-label" for="branch_' + branchId + '">' + this.location + '</label>' +
				'</div>'
			);
			$('#branch_' + branchId).change(function () {
				const checked = $(this).prop('checked');
				$('input[name="staff_id[]"]').filter(function () {
					return $(this).hasClass(String(branchId));
				}).prop('checked', checked);
			});
		});

		$.each(staffs, function (index) {
			const i = index + 1;
			$('.wrap_checkbox').append(
				'<div class="form-check mb-1 g-3 remove_checkbox" style="vertical-align: middle;">' +
					'<input class="form-check-input ' + this.branch + '" name="staff_id[]" type="checkbox" value="' + this.id + '" id="staff_' + i + '">' +
					'<label class="form-check-label" for="staff_' + i + '">' +
						this.username +
						' - ' +
						this.name +
						'&nbsp;&nbsp;&nbsp;[' +
						this.department +
						']' +
					'</label>' +
				'</div>'
			);
		});

		$('#checkAll').change(function () {
			$('input:checkbox').prop('checked', $(this).prop('checked'));
		});
	}).fail(function (jqXHR, textStatus, errorThrown) {
		console.log(textStatus, errorThrown);
	});
}

$('#from').datetimepicker({ ...config.datetimepicker,
    maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
})
.on('dp.change', function () {
	$('#to').datetimepicker('minDate', $('#from').val());
	$('.remove_checkbox').remove();
	renderCheckboxes();
});

$('#to').datetimepicker({ ...config.datetimepicker,
    maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
})
.on('dp.change', function () {
	$('#from').datetimepicker('maxDate', $('#to').val());
	$('.remove_checkbox').remove();
	renderCheckboxes();
});
