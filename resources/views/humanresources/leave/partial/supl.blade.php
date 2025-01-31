		$('#remove').remove();
		$('#wrapper').append(

			'<div id="remove">' +
				<!-- annual leave -->

				'<div class="form-group row m-2 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +

				'<div class="form-group row mb-3 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-2 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +

				'<div class="form-group row m-3 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
					'</div>' +
				'</div>' +

				@if( $userneedbackup == 99 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-2 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
						'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-control form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif

				'<div class="form-group row {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +

				'<div class="form-group row m-2 {{ $errors->has('akuan') ? 'has-error' : '' }}">' +
					'{{ Form::label('suppdoc', 'Supporting Document : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 suppdoc form-check">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input', 'id' => 'suppdoc']) }}' +
						' <label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Document</strong> within a period of  <strong>3 Days</strong> upon return.</label>' +
					'</div>' +
				'</div>' +

			'</div>'
			);
		/////////////////////////////////////////////////////////////////////////////////////////
		// add more option
		//add bootstrapvalidator
		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ \Auth::user()->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// start date
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fa-regular fa-circle-up fa-beat",
				down: "fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			disabledDates: data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// minDate: data[1],
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-6 m-2 removehalfleave " id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" disabled="disabled">' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" checked="checked">' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {date: datenow, _token: '{!! csrf_token() !!}', id: {{ \Auth::user()->belongstostaff->id }} },
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-2">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-2">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-6 m-2 removehalfleave " id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" checked="checked">' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" >' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			@if( $userneedbackup == 99 )
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(
						'<div class="form-group row {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
							'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
							'<div class="col-sm-8 backup">' +
								'<select name="staff_id" id="backupperson" class="form-control form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					$('#backupperson').select2({
						placeholder: 'Please Choose',
						width: '100%',
						ajax: {
							url: '{{ route('backupperson') }}',
							// data: { '_token': '{!! csrf_token() !!}' },
							type: 'POST',
							dataType: 'json',
							data: function (params) {
								var query = {
									id: {{ \Auth::user()->belongstostaff->id }},
									_token: '{!! csrf_token() !!}',
									date_from: $('#from').val(),
									date_to: $('#to').val(),
								}
								return query;
							}
						},
						allowClear: true,
						closeOnSelect: true,
					});
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			@endif
		});
		// end date from

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			disabledDates:data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-6 m-2 removehalfleave " id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" disabled="disabled">' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" checked="checked">' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {date: datenow, _token: '{!! csrf_token() !!}', id: {{ \Auth::user()->belongstostaff->id }} },
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-2">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-2">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
								'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
								'<div class="col-sm-8 m-2 removehalfleave " id="halfleave">' +
									'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
										'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" checked="checked">' +
										'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
									'</div>' +
									'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
										'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" >' +
										'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave m-2']) }}' +
									'</div>' +
								'</div>' +
								'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
								'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date to

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {date: datenow, _token: '{!! csrf_token() !!}', id: {{ \Auth::user()->belongstostaff->id }} },
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = jQuery.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" checked="checked">' +
							'<label for="am" class="form-check-label m-2">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>' +
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm">' +
							'<label for="pm" class="form-check-label m-2">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
			}
		});
	}
