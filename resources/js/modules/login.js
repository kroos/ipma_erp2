/* Login form validation — extracted from auth/login.blade.php */
$('#form').bootstrapValidator({
	fields: {
		username: {
			validators: {
				notEmpty: {
					message: 'Please insert username'
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
	}
});
