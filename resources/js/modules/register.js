/* Register form validation — extracted from auth/register.blade.php */
$('#form').bootstrapValidator({
	fields: {
		name: {
			validators: {
				notEmpty: {
					message: 'Please insert full name'
				},
			}
		},
		email: {
			validators: {
				notEmpty: {
					message: 'Please insert email'
				},
				emailAddress: {
					message: 'Please insert a valid email address'
				},
			}
		},
		password: {
			validators: {
				notEmpty : {
					message: 'Please insert password'
				},
			}
		},
		password_confirmation: {
			validators: {
				notEmpty : {
					message: 'Please confirm your password'
				},
				identical: {
					field: 'password',
					message: 'Password and confirmation do not match'
				},
			}
		},
	}
});
