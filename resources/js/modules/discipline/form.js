const { editId } = window.data;

/////////////////////////////////////////////////////////////////////////////////////////
$('.form-select').select2({ ...config.select2,
});


/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('#misconduct_date, #action_taken_date').datetimepicker({ ...config.datetimepicker,
    useCurrent: true,
});


/////////////////////////////////////////////////////////////////////////////////////////
// VALIDATOR
$(document).ready(function() {
	$('#form').bootstrapValidator({

		fields: {
			staff_id: {
				validators: {
					notEmpty: {
						message: 'Please select staff.'
					}
				}
			},

			supervisor_id: {
				validators: {
					notEmpty: {
						message: 'Please select supervisor incharge.'
					}
				}
			},

			disciplinary_action_id: {
				validators: {
					notEmpty: {
						message: 'Please select disciplinary action.'
					}
				}
			},

			violation_id: {
				validators: {
					notEmpty: {
						message: 'Please select violation.'
					}
				}
			},

			infraction_id: {
				validators: {
					notEmpty: {
						message: 'Please select infraction.'
					}
				}
			},

			misconduct_date: {
				validators: {
					notEmpty: {
						message: 'Please insert misconduct date.'
					}
				}
			},

			action_taken_date: {
				validators: {
					notEmpty: {
						message: 'Please insert action taken date.'
					}
				}
			},

			reason: {
				validators: {
					notEmpty: {
						message: 'Please insert incident description.'
					}
				}
			},

			action_to_be_taken: {
				validators: {
					notEmpty: {
						message: 'Please insert action to be taken.'
					}
				}
			},

			softcopy: {
				validators: {
					file: {
						extension: 'jpeg,jpg,png,bmp,pdf,doc,docx', // no space
						type: 'image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', // no space
						maxSize: 5242880, // 5120 * 1024,
						message: 'The selected file is not valid. Please use jpeg, jpg, png, bmp, pdf or doc and the file is below than 5MB.'
					},
				}
			},

		}
	})
});
