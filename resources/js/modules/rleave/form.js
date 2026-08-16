// Replacement Leave (create + edit) — check-all groups, select2, date pickers, validator
// Loaded for both rleave.create and rleave.edit (moduleLoader ACTION_MAP: create/edit -> form)

/////////////////////////////////////////////////////////////////////////////////////////
// CHECK ALL STAFF
$("#checkAll").change(function () {
	$(".staff").prop('checked', this.checked);
});

// CHECK ALL GROUP 1
$("#checkG1").change(function () {
	$(".group1").prop('checked', this.checked);
});

// CHECK ALL GROUP 2
$("#checkG2").change(function () {
	$(".group2").prop('checked', this.checked);
});

/////////////////////////////////////////////////////////////////////////////////////////
$('#customer_id').select2({ ...config.select2,
});

/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('#date_start, #date_end').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});

/////////////////////////////////////////////////////////////////////////////////////////
// VALIDATOR (fields present on the current page only)
$(document).ready(function() {
	var fields = {
		date_start: {
			validators: {
				notEmpty: {
					message: 'Please select a date.'
				}
			}
		},

		date_end: {
			validators: {
				notEmpty: {
					message: 'Please select a date.'
				}
			}
		},

		reason: {
			validators: {
				notEmpty: {
					message: 'Please insert a reason.'
				}
			}
		},
	};

	// create page only — staff checkbox list
	if ($('#form').find('[name="staff_id[]"]').length) {
		fields['staff_id[]'] = {
			validators: {
				notEmpty: {
					message: 'Please select a staff.'
				}
			}
		};
	}

	// edit page only — numeric day fields (guarded by name: the inputs previously had
	// copy-paste id="id", so an id-based check would never match)
	if ($('#form').find('[name="leave_total"]').length) {
		fields.leave_total = {
			validators: {
				notEmpty: {
					message: 'Please insert a value. 0 by default.'
				},
				numeric: {
					message: 'The value is not numeric'
				}
			}
		};
		fields.leave_utilize = {
			validators: {
				notEmpty: {
					message: 'Please insert a value. 0 by default.'
				},
				numeric: {
					message: 'The value is not numeric'
				}
			}
		};
		fields.leave_balance = {
			validators: {
				notEmpty: {
					message: 'Please insert a value. 0 by default.'
				},
				numeric: {
					message: 'The value is not numeric'
				}
			}
		};
	}

	$('#form').bootstrapValidator({
		feedbackIcons: {
			valid: '',
			invalid: '',
			validating: ''
		},
		fields: fields,
	});
});

/////////////////////////////////////////////////////////////////////////////////////////
// TOOLTIP
$(function () {
	$('[data-toggle="tooltip"]').tooltip({ ...config.tooltip })
});
