const data = window.data;
const { route, url, errors } = data;
const isEdit = !!data.isEdit;


/////////////////////////////////////////////////////////////////////////////////////////
// helper : replicate blade $errors->has() (supports wildcard like 'staffspouse.*.spouse')
function hasErr(key) {
	if (!errors) return '';
	if (key.indexOf('.*.') !== -1) {
		// 'staffspouse.*.spouse' -> /^staffspouse\.[^.]+\.spouse$/ — matches any 'staffspouse.N.spouse'
		const re = new RegExp('^' + key.replace('*', '[^.]+').replace(/\./g, '\\.') + '$');
		return Object.keys(errors).some(function (k) {
			return re.test(k);
		}) ? 'has-error' : '';
	}
	return errors[key] ? 'has-error' : '';
}

// highest row index currently in the DOM for a given field prefix (e.g. 'staffspouse[')
// — used after row removal so the add-counter never collides with a surviving row's index
function maxRowIndex(rowsSel, prefix) {
	var max = 0;
	$(rowsSel).find('[name^="' + prefix + '"]').each(function () {
		var m = this.name.match(/\[(\d+)\]/);
		if (m) {
			var n = parseInt(m[1], 10);
			if (n > max) {
				max = n;
			}
		}
	});
	return max;
}

// shared datetimepicker icons
const dtpIcons = {
	time: "fas fas-regular fa-clock fa-beat",
	date: "fas fas-regular fa-calendar fa-beat",
	up: "fa-regular fa-circle-up fa-beat",
	down: "fa-regular fa-circle-down fa-beat",
	previous: 'fas fas-regular fa-arrow-left fa-beat',
	next: 'fas fas-regular fa-arrow-right fa-beat',
	today: 'fas fas-regular fa-calenday-day fa-beat',
	clear: 'fas fas-regular fa-broom-wide fa-beat',
	close: 'fas fas-regular fa-rectangle-xmark fa-beat'
};

// shared select2 builders
function plainSelect2(placeholder) {
	return {
		...config.select2,
		placeholder: placeholder || 'Please Select',
	};
}

function ajaxSelect2(endpoint, extraDataFn) {
	return {
		...config.select2,
		placeholder: 'Please Select',
		ajax: {
			url: endpoint,
			type: 'POST',
			dataType: 'json',
			data: function (params) {
				var query = {
					search: params.term,
				};
				if (typeof extraDataFn === 'function') {
					query = Object.assign(query, extraDataFn());
				}
				return query;
			}
		},
	};
}

/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER IN
$('#dob, #jpo').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});

/////////////////////////////////////////////////////////////////////////////////////////
// select2 : static selects
$('#rel, #gen, #rac, #nat, #mar').select2(plainSelect2());

// maternity leave : show/hide on gender change (same on create & edit)
$('#gen_1').on('change', function () {
	if ($(this).val() == 2) {
		if ($('#append').length == 0) {
			$('#wrapmaternity').append(
				'<div id="append">' +
					'<div class="form-group row mb-3 ' + hasErr('maternity_leave') + '">' +
						'<label for="matl" id="matl" class="col-sm-4 col-form-label">Maternity Leave : </label>' +
						'<div class="col-auto">' +
							'<input type="text" name="maternity_leave" value="' + data.maternityLeave + '" id="matl" class="form-control form-control-sm col-auto" placeholder="Maternity Leave">' +
						'</div>' +
					'</div>' +
				'</div>'
			);
			$('#form').bootstrapValidator('addField', $('#append').find('[name="maternity_leave"]'));
		}
	}
});

$('#gen_0').on('change', function () {
	if ($(this).val() == 1) {
		$('#append').remove();
		$('#form').bootstrapValidator('removeField', $('#append').find('[name="maternity_leave"]'));
	}
});

/////////////////////////////////////////////////////////////////////////////////////////
// select2 : create uses ajax endpoints, edit uses server-rendered options
if (isEdit) {
	$('#sta, #cat, #bra, #him').select2(plainSelect2());
} else {
	$('#sta').select2(ajaxSelect2(route.status));
	$('#cat').select2(ajaxSelect2(route.category));
	$('#bra').select2(ajaxSelect2(route.branch));
	$('#him').select2(ajaxSelect2(route.division));
}

$('#cat').on('select2:select select2:unselect', function () {
	$('#dep').val(null).trigger('change');
});

$('#bra').on('select2:select select2:unselect', function () {
	$('#dep').val(null).trigger('change');
});

$('#dep').select2(ajaxSelect2(route.department, function () {
	return {
		branch_id: $('#bra').val(),
		category_id: $('#cat').val(),
	};
}));

$('#rdg').select2(ajaxSelect2(route.restdaygroup));

/////////////////////////////////////////////////////////////////////////////////////////
// cross backup : existing rows on edit (server-rendered options), first row on create
if (isEdit) {
	for (let i = 1; i <= data.crossbackupCount; i++) {
		$('#sta_' + i).select2(plainSelect2());
	}
} else {
	$('#sta_1').select2(ajaxSelect2(route.crossbackup));
}

/////////////////////////////////////////////////////////////////////////////////////////
// children : existing rows on edit get date pickers (select2 for existing is disabled)
if (isEdit) {
	for (let i = 1; i <= data.childrenCount; i++) {
		$('#cdo_' + i).datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});
	}
} else {
	// create : first-row pickers/selects (no-ops until a row exists, kept for parity)
	$('#ere_1').select2(ajaxSelect2(route.relationship));
}

/////////////////////////////////////////////////////////////////////////////////////////
// EDIT ONLY : delete handlers
if (isEdit) {

	// delete spouse
	$(document).on('click', '.spouse_delete', function (e) {
		var spouseId = $(this).data('id');
		SwalDelete(spouseId);
		e.preventDefault();
	});

	function SwalDelete(spouseId) {
		swal.fire({ ...config.swal,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    preConfirm: function () {
				return new Promise(function (resolve) {
					$.ajax({
						type: 'DELETE',
						url: url.spouse + '/' + spouseId,
						data: {
							id: spouseId,
						},
						dataType: 'json'
					})
						.done(function (response) {
							swal.fire('Deleted!', response.message, response.status)
								.then(function () {
									window.location.reload(true);
								});
						})
						.fail(function () {
							swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
						})
				});
			},
})
			.then((result) => {
				if (result.dismiss === swal.DismissReason.cancel) {
					swal.fire('Cancelled', 'Your data is safe from delete', 'info')
				}
			});
	}

	// delete children
	$(document).on('click', '.children_delete', function (e) {
		var childrenId = $(this).data('id');
		SwalChildDelete(childrenId);
		e.preventDefault();
	});

	function SwalChildDelete(childrenId) {
		swal.fire({ ...config.swal,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    preConfirm: function () {
				return new Promise(function (resolve) {
					$.ajax({
						type: 'DELETE',
						url: url.children + '/' + childrenId,
						data: {
							id: childrenId,
						},
						dataType: 'json'
					})
						.done(function (response) {
							swal.fire('Deleted!', response.message, response.status)
								.then(function () {
									window.location.reload(true);
								});
						})
						.fail(function () {
							swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
						})
				});
			},
})
			.then((result) => {
				if (result.dismiss === swal.DismissReason.cancel) {
					swal.fire('Cancelled', 'Your data is safe from delete', 'info')
				}
			});
	}

	// delete emergency contact
	$(document).on('click', '.emergency_delete', function (e) {
		var emergencyId = $(this).data('id');
		SwalEmergDelete(emergencyId);
		e.preventDefault();
	});

	function SwalEmergDelete(emergencyId) {
		swal.fire({ ...config.swal,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    preConfirm: function () {
				return new Promise(function (resolve) {
					$.ajax({
						type: 'DELETE',
						url: url.emergencycontact + '/' + emergencyId,
						data: {
							id: emergencyId,
						},
						dataType: 'json'
					})
						.done(function (response) {
							swal.fire('Deleted!', response.message, response.status)
								.then(function () {
									window.location.reload(true);
								});
						})
						.fail(function () {
							swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
						})
				});
			},
})
			.then((result) => {
				if (result.dismiss === swal.DismissReason.cancel) {
					swal.fire('Cancelled', 'Your data is safe from delete', 'info')
				}
			});
	}

	// delete crossbackup
	$(document).on('click', '.crossbackup_delete', function (e) {
		var crossbackupId = $(this).data('id');
		DeleteCrossBackUp(crossbackupId);
		e.preventDefault();
	});

	function DeleteCrossBackUp(crossbackupId) {
		swal.fire({ ...config.swal,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    preConfirm: function () {
				return new Promise(function (resolve) {
					$.ajax({
						type: 'DELETE',
						url: url.deletecrossbackup + '/' + data.staffId,
						data: {
							id: crossbackupId,
						},
						dataType: 'json'
					})
						.done(function (response) {
							swal.fire('Deleted!', response.message, response.status)
								.then(function () {
									window.location.reload(true);
								});
						})
						.fail(function () {
							swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
						})
				});
			},
})
			.then((result) => {
				if (result.dismiss === swal.DismissReason.cancel) {
					swal.fire('Cancelled', 'Your data is safe from delete', 'info')
				}
			});
	}
}

/////////////////////////////////////////////////////////////////////////////////////////
// add spouse : add and remove row

var max_fields = 4;							//maximum input boxes allowed
var add_buttons = $(".spouse_add");
var wrappers = $(".spouse_wrap");

var xs = isEdit ? data.spouseCount : 0;
$(add_buttons).click(function () {

	//max input box allowed
	if (xs < max_fields) {
		xs++;
		wrappers.append(

			'<div class="row m-1 spouse_row">' +
				'<div class="col-sm-1">' +
					'<button class="btn btn-sm btn-outline-secondary spouse_remove" type="button">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col-sm-11 form-group ' + hasErr('staffspouse.*.spouse') + '">' +
					(isEdit ? '<input type="hidden" name="staffspouse[' + xs + '][id]" value="">' : '') +
					'<input type="text" name="staffspouse[' + xs + '][spouse]" id="spo" class="form-control form-control-sm" placeholder="' + (isEdit ? 'Spouse' : 'Spouse Name') + '">' +
				'</div>' +
				'<div class="col-sm-1"></div>' +
				'<div class="col-sm-5 form-group ' + hasErr('staffspouse.*.phone') + '">' +
					'<input type="text" name="staffspouse[' + xs + '][phone]" id="pho" class="form-control form-control-sm" placeholder="Spouse Phone">' +
				'</div>' +
				'<div class="col-sm-6 form-group ' + hasErr('staffspouse.*.profession') + '">' +
					'<input type="text" name="staffspouse[' + xs + '][profession]" id="pro" class="form-control form-control-sm" placeholder="Spouse Profession">' +
				'</div>' +
			'</div>'

		); //add input box

		//bootstrap validate
		$('#form').bootstrapValidator('addField', $('.spouse_row').find('[name="staffspouse[' + xs + '][spouse]"]'));
		$('#form').bootstrapValidator('addField', $('.spouse_row').find('[name="staffspouse[' + xs + '][phone]"]'));
		$('#form').bootstrapValidator('addField', $('.spouse_row').find('[name="staffspouse[' + xs + '][profession]"]'));
	}
})

$(wrappers).on("click", ".spouse_remove", function (e) {
	//user click on remove text
	e.preventDefault();
	var $row = $(this).closest('.spouse_row');
	// use the row's own fields (not the global counter) so removing an
	// earlier row always removes the right inputs/validators
	var $fields = $row.find('[name^="staffspouse["]').filter(':not([type="hidden"])');
	$row.remove();

	$fields.each(function () {
		$('#form').bootstrapValidator('removeField', $(this));
	});
	xs = maxRowIndex('.spouse_row', 'staffspouse[');
})

/////////////////////////////////////////////////////////////////////////////////////////
// add children : add and remove row

var cmax_fields = 12;						//maximum input boxes allowed
var cadd_buttons = $(".children_add");
var cwrappers = $(".children_wrap");

var xc = isEdit ? data.childrenCount : 0;
$(cadd_buttons).click(function () {

	//max input box allowed
	if (xc < cmax_fields) {
		xc++;
		cwrappers.append(
			'<div class="row m-1 children_row">' +
				'<div class="col-sm-1">' +
					'<button class="btn btn-sm btn-outline-secondary children_remove" type="button">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col-sm-11 form-group ' + hasErr('staffchildren.*.children') + '">' +
					(isEdit ? '<input type="hidden" name="staffchildren[' + xc + '][id]" value="">' : '') +
					'<input type="text" name="staffchildren[' + xc + '][children]" id="chi_' + xc + '" class="form-control form-control-sm" placeholder="' + (isEdit ? 'Children' : 'Children Name') + '">' +
				'</div>' +
				'<div class="col-sm-1"></div>' +
				'<div class="col-sm-7 form-group ' + hasErr('staffchildren.*.dob') + '" style="position: relative">' +
					'<input type="text" name="staffchildren[' + xc + '][dob]" value="" id="cdo_' + xc + '" class="form-control form-control-sm" placeholder="Date Of Birth">' +
				'</div>' +
				'<div class="col-sm-4 form-group ' + hasErr('staffchildren.*.gender_id') + '">' +
					'<select name="staffchildren[' + xc + '][gender_id]" id="cge_' + xc + '" class="form-select form-select-sm" placeholder="Gender"></select>' +
				'</div>' +
				'<div class="col-sm-1"></div>' +
				'<div class="col-sm-7 form-group ' + hasErr('staffchildren.*.education_level_id') + '">' +
					'<select name="staffchildren[' + xc + '][education_level_id]" id="cel_' + xc + '" class="form-select form-select-sm" placeholder="Education Level"></select>' +
				'</div>' +
				'<div class="col-sm-4 form-group ' + hasErr('staffchildren.*.health_status_id') + '">' +
					'<select name="staffchildren[' + xc + '][health_status_id]" id="chs_' + xc + '" class="form-select form-select-sm" placeholder="Health Status"></select>' +
				'</div>' +
				'<div class="col-sm-1"></div>' +
				'<div class="col-sm-5 form-group form-check ' + hasErr('staffchildren.*.tax_exemption') + '">' +
					'<input type="hidden" name="staffchildren[' + xc + '][tax_exemption]" class="form-check-input" value="0">' +
					'<input type="checkbox" name="staffchildren[' + xc + '][tax_exemption]" class="form-check-input" value="1" id="cte_' + xc + '">' +
					'<label class="form-check-label" for="cte_' + xc + '">Valid for Tax Exemption?</label>' +
				'</div>' +
				'<div class="col-sm-6 form-group ' + hasErr('staffchildren.*.tax_exemption_percentage_id') + '">' +
					'<select name="staffchildren[' + xc + '][tax_exemption_percentage_id]" id="ctep_' + xc + '" class="form-select form-select-sm" placeholder="Tax Exemption Percentage"></select>' +
				'</div>' +
			'</div>'
		); //add input box

		$('#cge_' + xc + '').select2(ajaxSelect2(route.gender));
		$('#cel_' + xc + '').select2(ajaxSelect2(route.educationlevel));
		$('#chs_' + xc + '').select2(ajaxSelect2(route.healthstatus));
		$('#ctep_' + xc + '').select2(ajaxSelect2(route.taxexemptionpercentage));

		$('#cdo_' + xc).datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});

		//bootstrap validate
		$('#form').bootstrapValidator('addField', $('.children_row').find('[name="staffchildren[' + xc + '][children]"]'));
		$('#form').bootstrapValidator('addField', $('.children_row').find('[name="staffchildren[' + xc + '][gender_id]"]'));
		$('#form').bootstrapValidator('addField', $('.children_row').find('[name="staffchildren[' + xc + '][education_level_id]"]'));
		$('#form').bootstrapValidator('addField', $('.children_row').find('[name="staffchildren[' + xc + '][health_status_id]"]'));
		$('#form').bootstrapValidator('addField', $('.children_row').find('[name="staffchildren[' + xc + '][tax_exemption]"]'));
		$('#form').bootstrapValidator('addField', $('.children_row').find('[name="staffchildren[' + xc + '][tax_exemption_percentage_id]"]'));
	}
})

$(cwrappers).on("click", ".children_remove", function (e) {
	//user click on remove text
	e.preventDefault();
	var $row = $(this).closest('.children_row');
	// use the row's own fields (not the global counter) so removing an
	// earlier row always removes the right inputs/validators
	var $fields = $row.find('[name^="staffchildren["]').filter(':not([type="hidden"])');
	$row.remove();

	$fields.each(function () {
		$('#form').bootstrapValidator('removeField', $(this));
	});
	xc = maxRowIndex('.children_row', 'staffchildren[');
})

/////////////////////////////////////////////////////////////////////////////////////////
// add emergency : add and remove row

var emax_fields = 3;						//maximum input boxes allowed
var eadd_buttons = $(".emergency_add");
var ewrappers = $(".emergency_wrap");

var xe = isEdit ? data.emergencyCount : 1;
$(eadd_buttons).click(function () {

	//max input box allowed
	if (xe < emax_fields) {
		xe++;
		ewrappers.append(
			'<div class="row m-1 emergency_row">' +
				'<div class="col-sm-1">' +
					'<button class="btn btn-sm btn-outline-secondary emergency_remove" type="button">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col-sm-11 form-group ' + hasErr('staffemergency.*.contact_person') + '">' +
					(isEdit ? '<input type="hidden" name="staffemergency[' + xe + '][id]" value="">' : '') +
					'<input type="text" name="staffemergency[' + xe + '][contact_person]" id="ecp_' + xe + '" class="form-control form-control-sm" placeholder="' + (isEdit ? 'Emergency Contact' : 'Name') + '">' +
				'</div>' +
				'<div class="col-sm-1"></div>' +
				'<div class="col-sm-5 form-group ' + hasErr('staffemergency.*.phone') + '">' +
					'<input type="text" name="staffemergency[' + xe + '][phone]" id="epp_' + xe + '" class="form-control form-control-sm" placeholder="Phone">' +
				'</div>' +
				'<div class="col-sm-6 form-group ' + hasErr('staffemergency.*.relationship_id') + '">' +
					'<select name="staffemergency[' + xe + '][relationship_id]" id="ere_' + xe + '" class="form-select form-select-sm" placeholder="Relationship"></select>' +
				'</div>' +
				'<div class="col-sm-1"></div>' +
				'<div class="col-sm-11 form-group ' + hasErr('staffemergency.*.address') + '">' +
					'<input type="textarea" name="staffemergency[' + xe + '][address]" id="ead_' + xe + '" class="form-control form-control-sm" placeholder="Address">' +
				'</div>' +
			'</div>'
		); //add input box

		$('#ere_' + xe + '').select2(ajaxSelect2(route.relationship));

		//bootstrap validate
		$('#form').bootstrapValidator('addField', $('.emergency_row').find('[name="staffemergency[' + xe + '][contact_person]"]'));
		$('#form').bootstrapValidator('addField', $('.emergency_row').find('[name="staffemergency[' + xe + '][phone]"]'));
		$('#form').bootstrapValidator('addField', $('.emergency_row').find('[name="staffemergency[' + xe + '][relationship_id]"]'));
		$('#form').bootstrapValidator('addField', $('.emergency_row').find('[name="staffemergency[' + xe + '][address]"]'));
	}
})

$(ewrappers).on("click", ".emergency_remove", function (e) {
	//user click on remove text
	e.preventDefault();
	var $row = $(this).closest('.emergency_row');
	// use the row's own fields (not the global counter) so removing an
	// earlier row always removes the right inputs/validators
	var $fields = $row.find('[name^="staffemergency["]').filter(':not([type="hidden"])');
	$row.remove();

	$fields.each(function () {
		$('#form').bootstrapValidator('removeField', $(this));
	});
	xe = maxRowIndex('.emergency_row', 'staffemergency[');
})

/////////////////////////////////////////////////////////////////////////////////////////
// add cross backup : add and remove row

var crb_max_fields = 5;						//maximum input boxes allowed
var crb_add_buttons = $(".crossbackup_add");
var crb_wrappers = $(".crossbackup_wrap");

var xcrb = isEdit ? data.crossbackupCount : 1;
$(crb_add_buttons).click(function () {

	//max input box allowed
	if (xcrb < crb_max_fields) {
		xcrb++;
		crb_wrappers.append(
			'<div class="row m-1 p-0 crossbackup_row">' +
					'<div class="col-sm-1">' +
						'<button class="btn btn-sm btn-outline-secondary crossbackup_remove" type="button">' +
							'<i class="fas fa-trash" aria-hidden="true"></i>' +
						'</button>' +
					'</div>' +
					'<div class="col-sm-10 form-group ' + hasErr('crossbackup.*.backup_staff_id') + '">' +
						(!isEdit ? '<input type="hidden" name="crossbackup[' + xcrb + '][active]" value="1">' : '') +
						'<select name="crossbackup[' + xcrb + '][backup_staff_id]" id="sta_' + xcrb + '" class="form-select form-select-sm" placeholder="Cross Backup Personnel"></select>' +
					'</div>' +
				'</div>'
		);

		$('#sta_' + xcrb).select2(ajaxSelect2(route.crossbackup));

		//bootstrap validate
		$('#form').bootstrapValidator('addField', $('.crossbackup_row').find('[name="crossbackup[' + xcrb + '][backup_staff_id]"]'));
	}
})

$(crb_wrappers).on("click", ".crossbackup_remove", function (e) {
	//user click on remove text
	e.preventDefault();
	var $row = $(this).closest('.crossbackup_row');
	// use the row's own fields (not the global counter) so removing an
	// earlier row always removes the right inputs/validators
	var $fields = $row.find('[name^="crossbackup["]').filter(':not([type="hidden"])');
	$row.remove();

	$fields.each(function () {
		$('#form').bootstrapValidator('removeField', $(this));
	});
	xcrb = maxRowIndex('.crossbackup_row', 'crossbackup[');
})

/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator

const validatorFields = {

	username: {
		validators: {
			notEmpty: {
				message: 'Please insert username. '
			},
			remote: !isEdit ? {
				type: 'POST',
				url: route.loginuser,
				message: 'Username exist. Please use another username. ',
				data: function (validator) {
					return {
						username: $('#unam').val(),
					};
				},
				delay: 1,		// wait 0.001 seconds
			} : undefined,
		}
	},

	password: {
		validators: {
			notEmpty: !isEdit ? {
				message: 'Please insert password. '
			} : undefined,
		}
	},

	status_id: {
		validators: {
			notEmpty: {
				message: 'Please choose. '
			},
		}
	},

	category_id: {
		validators: {
			notEmpty: {
				message: 'Please choose. '
			},
		}
	},

	branch_id: {
		validators: {
			notEmpty: {
				message: 'Please choose. '
			},
		}
	},

	pivot_dept_id: {
		validators: {
			notEmpty: {
				field: 'category_id',
				message: 'Please choose. '
			},
		}
	},

	div_id: {
		validators: {},
	},

	restday_group_id: {
		validators: {
			notEmpty: isEdit ? {
				message: 'Please choose. '
			} : undefined,
		}
	},

	authorise_id: {
		validators: {},
	},

	leave_flow_id: {
		validators: {
			notEmpty: {
				message: 'Please choose. '
			},
		}
	},

	annual_leave: {
		validators: {
			notEmpty: isEdit ? {
				message: 'Please choose. '
			} : undefined,
			numeric: {
				separator: '.',
				message: 'Numbers must be in decimal ',
			},
			step: {
				baseValue: 0,
				step: 0.5,
				message: 'Number increase must be in 0.5 ',
			},
		}
	},

	mc_leave: {
		validators: {
			notEmpty: isEdit ? {
				message: 'Please choose. '
			} : undefined,
			numeric: {
				separator: '.',
				message: 'Numbers must be in decimal ',
			},
			step: {
				baseValue: 0,
				step: 0.5,
				message: 'Number increase must be in 0.5 ',
			},
		}
	},

	maternity_leave: {
		validators: {
			numeric: {
				separator: '.',
				message: 'Numbers must be in decimal ',
			},
			step: {
				baseValue: 0,
				step: 0.5,
				message: 'Number increase must be in 0.5 ',
			},
		}
	},

	name: {
		validators: {
			notEmpty: {
				message: 'Please insert new staff name. '
			},
		}
	},

	ic: {
		validators: {
			notEmpty: {
				message: 'Please insert Identity Card or Passport. '
			},
			remote: !isEdit ? {
				type: 'POST',
				url: route.icuser,
				message: 'Identity Card or Passport exist, please activate this person ',
				data: function (validator) {
					return {
						ic: $('#ic').val(),
					};
				},
				delay: 1,		// wait 0.001 seconds
			} : undefined,
		}
	},

	religion_id: {
		validators: {},
	},

	gender_id: {
		validators: {
			notEmpty: {
				message: 'Please select. '
			},
		}
	},

	race_id: {
		validators: {},
	},

	nationality_id: {
		validators: {},
	},

	marital_status_id: {
		validators: {
			notEmpty: {
				message: 'Please select. '
			},
		}
	},

	email: {
		validators: {
			notEmpty: {
				message: 'Please insert email. '
			},
			emailAddress: {
				message: 'Please insert valid email '
			},
			remote: !isEdit ? {
				type: 'POST',
				url: route.emailuser,
				message: 'Email exist, please use another email ',
				data: function (validator) {
					return {
						email: $('#email').val(),
					};
				},
				delay: 1,		// wait 0.001 seconds
			} : undefined,
		}
	},

	address: {
		validators: {
			notEmpty: {
				message: 'Please insert address. '
			},
		}
	},

	mobile: {
		validators: {
			notEmpty: {
				message: 'Please insert mobile. '
			},
			digits: {
				message: 'Please insert valid mobile number '
			},
		}
	},

	phone: {
		validators: {
			digits: {
				message: 'Please insert valid mobile number '
			},
		}
	},

	dob: {
		validators: {
			date: {
				format: 'YYYY-MM-DD',
				message: isEdit ? 'Please insert valid mobile number ' : 'Invalid date '
			},
		}
	},

	cimb_account: {
		validators: {
			digits: {
				message: 'Please insert valid mobile number '
			},
		}
	},

	epf_account: {
		validators: {
			digits: {
				message: 'Please insert valid mobile number '
			},
		}
	},

	income_tax_no: {
		validators: {},
	},

	socso_no: {
		validators: {
			digits: {
				message: 'Please insert valid mobile number '
			},
		}
	},

	weight: {
		validators: {
			numeric: {
				separator: '.',
				message: 'Only numbers. '
			},
		}
	},

	height: {
		validators: {
			numeric: {
				separator: '.',
				message: 'Only numbers. '
			},
		}
	},

	join: {
		validators: {
			date: {
				format: 'YYYY-MM-DD',
				message: isEdit ? 'The value is not a valid date. ' : 'Invalid date '
			},
		}
	},

	confirmed: {
		validators: {
			date: {
				format: 'YYYY-MM-DD',
				message: isEdit ? 'The value is not a valid date. ' : 'Invalid date '
			},
		}
	},

	image: {
		validators: {
			file: {
				extension: 'jpeg,jpg,png,bmp',
				type: 'image/jpeg,image/png,image/bmp',
				maxSize: 2097152,	// 2048 * 1024,
				message: 'The selected file is not valid. Please use jpeg or png and the image is below than 3MB. '
			},
		}
	},
};

// spouse (1..4)
for (let ie = 1; ie <= 4; ie++) {
	validatorFields['staffspouse[' + ie + '][spouse]'] = {
		validators: {
			regexp: {
				regexp: /^[a-z\s'@]+$/i,
				message: "The full name can consist of alphabetical characters, ', @ and spaces only"
			},
		}
	};
	validatorFields['staffspouse[' + ie + '][phone]'] = {
		validators: {
			digits: {
				message: isEdit ? 'Only numbers. ' : 'Please insert valid phone number '
			},
		}
	};
	validatorFields['staffspouse[' + ie + '][profession]'] = {
		validators: {},
	};
}

// children (1..4)
for (let ic = 1; ic <= 4; ic++) {
	validatorFields['staffchildren[' + ic + '][children]'] = {
		validators: {
			regexp: {
				regexp: /^[a-z\s'@]+$/i,
				message: "The full name can consist of alphabetical characters, ', @ and spaces only"
			},
		}
	};
	validatorFields['staffchildren[' + ic + '][gender_id]'] = {
		validators: {},
	};
	validatorFields['staffchildren[' + ic + '][education_level_id]'] = {
		validators: {},
	};
	validatorFields['staffchildren[' + ic + '][health_status_id]'] = {
		validators: {},
	};
	validatorFields['staffchildren[' + ic + '][tax_exemption]'] = {
		validators: {},
	};
}

// emergency (1..4)
for (let ie = 1; ie <= 4; ie++) {
	validatorFields['staffemergency[' + ie + '][contact_person]'] = {
		validators: {
			notEmpty: !isEdit ? {
				message: 'Please insert emergency contact person '
			} : undefined,
			regexp: {
				regexp: /^[a-z\s'@]+$/i,
				message: "The full name can consist of alphabetieal characters, ', @ and spaces only"
			},
		}
	};
	validatorFields['staffemergency[' + ie + '][phone]'] = {
		validators: {
			notEmpty: !isEdit ? {
				message: 'Please insert emergency contact person phone. '
			} : undefined,
			digits: {
				message: 'Please insert valid phone number '
			},
		}
	};
	validatorFields['staffemergency[' + ie + '][relationship_id]'] = {
		validators: {
			notEmpty: !isEdit ? {
				message: 'Please insert emergency contact person profession. '
			} : undefined,
		}
	};
	validatorFields['staffemergency[' + ie + '][address]'] = {
		validators: {},
	};
}

// crossbackup (1..5)
for (let ie = 1; ie <= 5; ie++) {
	validatorFields['crossbackup[' + ie + '][backup_staff_id]'] = {
		validators: {},
	};
}

// strip undefined validators so the plugin never sees an empty "remote" key
Object.keys(validatorFields).forEach(function (key) {
	const v = validatorFields[key].validators;
	Object.keys(v).forEach(function (vk) {
		if (v[vk] === undefined) {
			delete v[vk];
		}
	});
});

$('#form').bootstrapValidator({
	fields: validatorFields
});
