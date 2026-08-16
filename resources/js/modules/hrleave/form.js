const { route, url, errors } = window.data;
const old = (k, d) => {
	const v = window.data.old && window.data.old[k];
	return v !== undefined && v !== null ? v : (d !== undefined ? d : '');
};

/////////////////////////////////////////////////////////////////////////////////////////
// replacement leave options (built from server data)
let replacementOptions = '';
let replacementTotal = 0;
window.data.replacement.forEach(function(po) {
	replacementTotal += parseInt(po.leave_balance);
	replacementOptions += `<option value="${po.id}" data-nrlbalance="${po.leave_balance}" ${(po.id == window.data.replacementSelected) ? 'selected' : ''}>On ${moment(po.date_start, 'YYYY-MM-DD').format('ddd Do MMM YYYY')}, your leave balance = ${po.leave_balance} day</option>`;
});

/////////////////////////////////////////////////////////////////////////////////////////
$('#leave_id').select2({ ...config.select2,
    ajax: {
		url: route.leaveType,
		type: 'POST',
		dataType: 'json',
		data: function () {
			var data = {
				id: window.data.ownerId,
			}
			return data;
		}
	},
});

/////////////////////////////////////////////////////////////////////////////////////////
//  global variable : ajax to get the unavailable date
function getUnavailableDates(type) {
	var result;
	$.ajax({
		url: route.unavailabledate,
		type: "POST",
		data: {
			id: window.data.staffId,
			type: type,
		},
		dataType: 'json',
		async: false, // synchronous
		success: function (response) {
			// response is already parsed into JS array/object
			result = response;
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			result = []; // fallback in case of error
		}
	});
	return result;
}

/////////////////////////////////////////////////////////////////////////////////////////
// checking for overlapp leave on half day (if it is turn on)
function getUnblockhalfdayleave() {
	var result;
	$.ajax({
		url: route.unblockhalfdayleave,
		type: "POST",
		data: {
			id: window.data.staffId,
		},
		dataType: 'json',
		async: false, // synchronous
		success: function (response) {
			// response is already parsed into JS array/object
			result = response;
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			result = []; // fallback in case of error
		}
	});
	return result;
}

/////////////////////////////////////////////////////////////////////////////////////////
const datetimeIcons = {
	time: "fas fa-regular fa-clock fa-beat",
	date: "fas fa-regular fa-calendar fa-beat",
	up: "fa-regular fa-circle-up fa-beat",
	down: "fa-regular fa-circle-down fa-beat",
	previous: 'fas fa-regular fa-arrow-left fa-beat',
	next: 'fas fa-regular fa-arrow-right fa-beat',
	today: 'fas fa-regular fa-calendar-day fa-beat',
	clear: 'fas fa-regular fa-broom-wide fa-beat',
	close: 'fas fa-regular fa-rectangle-xmark fa-beat'
};

/////////////////////////////////////////////////////////////////////////////////////////
function getTimeLeave(date) {
	let result = null;
	$.ajax({
		url: route.timeleave,
		type: "POST",
		data: {
			date: date,
			id: window.data.staffId
		},
		dataType: 'json',
		async: false, // blocking
		success: function (response) {
			result = response;
		},
		error: function(xhr, status, error) {
			console.error("Error fetching timeleave:", status, error);
		}
	});
	return result;
}

/////////////////////////////////////////////////////////////////////////////////////////
function initBackupPerson(selector = '#backupperson', df = '#from', dt = '#to') {
	$(selector).select2({ ...config.select2,
    ajax: {
			url: route.backupperson,
			type: 'POST',
			dataType: 'json',
			data: function () {
				return {
					id: window.data.staffId,
					date_from: $(df).val(),
					date_to: $(dt).val()
				};
			}
		},
});
}

/////////////////////////////////////////////////////////////////////////////////////////
function getHalfdayInfo(selector) {
	let d = false, itime_start = 0, itime_end = 0;
	$.each(getUnblockhalfdayleave(), function() {
		if (this.date_half_leave == selector.val()) {
			d = true;
			itime_start = this.time_start;
			itime_end = this.time_end;
			return false; // break
		}
	});
	return [d, itime_start, itime_end];
};

// let [d, itime_start, itime_end] = getHalfdayInfo(date);

/////////////////////////////////////////////////////////////////////////////////////////
let from = `
	<div class="form-group row m-2 ${errors.date_time_start ? 'has-error' : ''}">
		<label for="from" class="col-sm-4 col-form-label">From : </label>
		<div class="col-sm-8 datetime" style="position: relative">
			<input type="text" name="date_time_start" value="${old('date_time_start')}" id="from" class="form-control form-control-sm ${errors.date_time_start ? 'is-invalid' : ''}" placeholder="From">
		</div>
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
let to = `
	<div class="form-group row m-2 ${errors.date_time_end ? 'has-error' : ''}">
		<label for="to" class="col-sm-4 col-form-label">To : </label>
		<div class="col-sm-8 datetime" style="position: relative">
			<input type="text" name="date_time_end" value="${old('date_time_end')}" id="to" class="form-control form-control-sm ${errors.date_time_end ? 'is-invalid' : ''}" placeholder="To">
		</div>
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
let timeOffHtml = `
<div class="form-group row m-2 ${errors.time_start ? 'has-error' : ''}">
	<label for="to" class="col-sm-4 col-form-label">Time : </label>
	<div class="col-sm-8">
		<div class="form-row time">
			<div class="col-sm-8 m-2" style="position: relative">
				<input type="text" name="time_start" value="${old('time_start')}" id="start" class="form-control form-control-sm ${errors.time_start ? 'is-invalid' : ''}" placeholder="Time Start">
			</div>
			<div class="col-sm-8 m-2" style="position: relative">
				<input type="text" name="time_end" value="${old('time_end')}" id="end" class="form-control form-control-sm ${errors.time_end ? 'is-invalid' : ''}" placeholder="Time End">
			</div>
		</div>
	</div>
</div>`;

/////////////////////////////////////////////////////////////////////////////////////////
let wrapperday = `
	<div class="form-group row m-2 ${errors.leave_cat ? 'has-error' : ''}" id="wrapperday">
		<div class="form-group col-sm-8 offset-sm-4 form-check ${errors.half_type_id ? 'has-error' : ''} removehalfleave"  id="wrappertest">
		</div>
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
let leave_cat = `
	<label for="leave_cat" class="col-sm-4 col-form-label removehalfleave">Leave Category : </label>
	<div class="col-sm-8 m-0 removehalfleave" id="halfleave">
		<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">
			<input type="radio" name="leave_cat" value="1" id="radio1" class="form-check-input removehalfleave ${errors.leave_cat ? 'is-invalid' : ''}" ${(window.data.hrleave.leave_cat == 1 || window.data.hrleave.leave_cat === null) ? 'checked' : ''}>
			<label for="radio1" class="form-check-label removehalfleave m-2 my-auto">Full Day Off</label>
		</div>
		<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">
			<input type="radio" name="leave_cat" value="2" id="radio2" class="form-check-input removehalfleave ${errors.leave_cat ? 'is-invalid' : ''}">
			<label for="radio2" class="form-check-label removehalfleave m-2 my-auto">Half Day Off</label>
		</div>
	</div>
	<div class="form-group col-sm-8 offset-sm-4 ${errors.half_type_id ? 'has-error' : ''} removehalfleave"  id="wrappertest">
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
function toggle_time_checkedam(obj) {
	return `
	<div class="form-check form-check-inline removetest">
		<input type="radio" name="half_type_id" value="1/${obj.time_start_am}/${obj.time_end_am}" id="am" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${toggle_time_start_am} ${checkedam}>
		<label for="am" class="form-check-label m-2 my-auto">
			${moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a')}
		</label>
	</div>
	<div class="form-check form-check-inline removetest">
		<input type="radio" name="half_type_id" value="2/${obj.time_start_pm}/${obj.time_end_pm}" id="pm" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${toggle_time_start_pm} ${checkedpm}>
		<label for="pm" class="form-check-label m-2 my-auto">
			${moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a')}
		</label>
	</div>
	`;
};

/////////////////////////////////////////////////////////////////////////////////////////
function toggle_time_hrleave(obj) {
	return `
	<div class="form-check form-check-inline removetest">
		<input type="radio" name="half_type_id" value="1/${obj.time_start_am}/${obj.time_end_am}" id="am" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${window.data.hrleave.half_type_id == 1 ? 'checked=checked' : ''}>
		<label for="am" class="form-check-label m-2 my-auto">
			${moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a')}
		</label>
	</div>
	<div class="form-check form-check-inline removetest">
		<input type="radio" name="half_type_id" value="2/${obj.time_start_pm}/${obj.time_end_pm}" id="pm" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${window.data.hrleave.half_type_id == 2 ? 'checked=checked' : ''}>
		<label for="pm" class="form-check-label m-2 my-auto">
			${moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a')}
		</label>
	</div>
	`;
};

/////////////////////////////////////////////////////////////////////////////////////////
let replacementForm = `
	<div class="form-group row m-2 ${errors.nrla ? 'has-error' : ''}">
		<label for="nrla" class="col-sm-4 col-form-label">Please Choose Your Replacement Leave : </label>
		<div class="col-sm-8 nrl">
			<p>Total Replacement Leave = ${replacementTotal} days</p>
			<select name="id" id="nrla" class="form-select form-select-sm ${errors.id ? 'is-invalid' : ''}">
				<option value="">Please select</option>
			${replacementOptions}
			</select>
		</div>
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
let userneedbackup = `
	<div class="form-group row m-2 ${errors.staff_id ? 'has-error' : ''}">
		<label for="backupperson" class="col-sm-4 col-form-label">Replacement : </label>
		<div class="col-sm-8 backup">
			<select name="staff_id" id="backupperson" class="form-select form-select-sm ${errors.staff_id ? 'is-invalid' : ''}" placeholder="Please choose" autocomplete="off">
				${staffOptions}
			</select>
		</div>
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
let doc = `
	<div class="form-group row m-2 ${errors.document ? 'has-error' : ''}">
		<label for="doc" class="col-sm-4 col-form-label">Upload Supporting Document : </label>
		<div class="col-sm-8 supportdoc">
			<input type="file" name="document" id="doc" class="form-control form-control-sm form-control-file ${errors.document ? 'is-invalid' : ''}" placeholder="Supporting Document">
		</div>
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
let suppdoc = `
	<div class="form-group row m-2 ${errors.documentsupport ? 'has-error' : ''}">
		<div class="offset-sm-4 col-sm-8 form-check">
			<input type="checkbox" name="documentsupport" value="1" id="suppdoc" class="form-check-input ${errors.documentsupport ? 'is-invalid' : ''}">
			<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</label>
		</div>
	</div>
`;

/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
function initDatepicker(selector) {
	let options = {
		icons: datetimeIcons,
		format:'YYYY-MM-DD',
		useCurrent: false,
	};
	return $(selector).datetimepicker(options);
}
/////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////
// start setting up the leave accordingly.
/////////////////////////////////////////////////////////////////////////////////////////
$(document).ready(function(){
	if ($('#leave_id').val() == '9') {													// if TF
		$('#wrapper').append(`
			<div id="remove">
				<div class="form-group row m-2 ${errors.date_time_start ? 'has-error' : ''}">
					<label for="from" class="col-sm-4 col-form-label">From : </label>
					<div class="col-sm-8 datetime" style="position: relative">
						<input type="text" name="date_time_start" value="${old('date_time_start', window.data.hrleave.date_time_start)}" id="from" class="form-control form-control-sm ${errors.date_time_start ? 'is-invalid' : ''}" placeholder="From">
					</div>
				</div>

				<div class="form-group row m-2 ${errors.time_start ? 'has-error' : ''}">
					<label for="to" class="col-sm-4 col-form-label">Time : </label>
					<div class="col-sm-8">
						<div class="form-row time">
							<div class="col-sm-8 m-2" style="position: relative">
								<input type="text" name="time_start" value="${old('time_start')}" id="start" class="form-control form-control-sm ${errors.time_start ? 'is-invalid' : ''}" placeholder="Time Start">
							</div>
							<div class="col-sm-8 m-2" style="position: relative">
								<input type="text" name="time_end" value="${old('time_end')}" id="end" class="form-control form-control-sm ${errors.time_end ? 'is-invalid' : ''}" placeholder="Time End">
							</div>
						</div>
					</div>
				</div>
				${(window.data.userneedbackup == 1 && window.data.backup) ? `
				<div class="form-group row m-2 ${errors.staff_id ? 'has-error' : ''}">
					<label for="backupperson" class="col-sm-4 col-form-label">Replacement : </label>
					<div class="col-sm-8 backup">
						<select name="staff_id" id="backupperson" class="form-select form-select-sm ${errors.staff_id ? 'is-invalid' : ''}" placeholder="Please choose" autocomplete="off">
							${staffOptions}
						</select>
					</div>
				</div>
				` : ''}

				<div class="form-group row m-2 ${errors.document ? 'has-error' : ''}">
					<label for="doc" class="col-sm-4 col-form-label">Upload Supporting Document : </label>
					<div class="col-sm-8 supportdoc">
						<input type="file" name="document" id="doc" class="form-control form-control-sm form-control-file ${errors.document ? 'is-invalid' : ''}" placeholder="Supporting Document">
					</div>
				</div>

				<div class="form-group row m-2 ${errors.documentsupport ? 'has-error' : ''}">
					<div class="offset-sm-4 col-sm-8 form-check">
						<input type="checkbox" name="documentsupport" value="1" id="suppdoc" class="form-check-input ${errors.documentsupport ? 'is-invalid' : ''}">
						<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</label>
					</div>
				</div>
			</div>`
		);
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
	} else {																			// other than TF

		var datenow = window.data.dateTimeStartYmd;

		// convert data1 into json
		var obj = getTimeLeave(datenow);

		$('#wrapper').append(`
			<div id="remove">

				${(window.data.hrleave.leave_type_id == 4 || window.data.hrleave.leave_type_id == 10) ? `
				<div class="form-group row m-2 ${errors.nrla ? 'has-error' : ''}">
					<label for="nrla" class="col-sm-4 col-form-label">Please Choose Your Replacement Leave : </label>
					<div class="col-sm-8 nrl">
						<p>Total Replacement Leave = ${replacementTotal} days</p>
						<select name="id" id="nrla" class="form-select form-select-sm ${errors.id ? 'is-invalid' : ''}">
							<option value="">Please select</option>
						${replacementOptions}
						</select>
					</div>
				</div>
				` : ''}

				<div class="form-group row m-2 ${errors.date_time_start ? 'has-error' : ''}">
					<label for="from" class="col-sm-4 col-form-label">From : </label>
					<div class="col-sm-8 datetime" style="position: relative">
						<input type="text" name="date_time_start" value="${old('date_time_start', window.data.hrleave.date_time_start)}" id="from" class="form-control form-control-sm ${errors.date_time_start ? 'is-invalid' : ''}" placeholder="From">
					</div>
				</div>

				<div class="form-group row m-2 ${errors.date_time_end ? 'has-error' : ''}">
					<label for="to" class="col-sm-4 col-form-label">To : </label>
					<div class="col-sm-8 datetime" style="position: relative">
						<input type="text" name="date_time_end" value="${old('date_time_end', window.data.hrleave.date_time_end)}" id="to" class="form-control form-control-sm ${errors.date_time_end ? 'is-invalid' : ''}" placeholder="To">
					</div>
				</div>

				<div class="form-group row m-2 ${errors.leave_cat ? 'has-error' : ''}" id="wrapperday">
					${window.data.hrleave.period_day <= 1 ? `
						<label for="leave_cat" class="col-sm-4 col-form-label removehalfleave">Leave Category : </label>
						<div class="col-sm-8 m-0 removehalfleave" id="halfleave">
							<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">
								<input type="radio" name="leave_cat" value="1" id="radio1" class="form-check-input removehalfleave ${errors[''] ? 'is-invalid' : ''}" ${(window.data.hrleave.leave_cat == 1 || window.data.hrleave.leave_cat === null) ? 'checked' : ''}>
								<label for="radio1" class="form-check-label removehalfleave m-2 my-auto">Full Day Off</label>
							</div>
							<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">
								<input type="radio" name="leave_cat" value="2" id="radio2" class="form-check-input removehalfleave ${errors[''] ? 'is-invalid' : ''}" ${window.data.hrleave.leave_cat == 2 ? 'checked' : ''}>
								<label for="radio2" class="form-check-label removehalfleave m-2 my-auto">Half Day Off</label>
							</div>
						</div>
						<div class="form-group col-sm-8 offset-sm-4 ${errors.half_type_id ? 'has-error' : ''} removehalfleave"  id="wrappertest">
							${window.data.hrleave.period_day <= 0.5 ? `
								<div class="form-check form-check-inline removetest">
									<input type="radio" name="half_type_id" value="1/${obj.time_start_am}/${obj.time_end_am}" id="am" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${window.data.hrleave.half_type_id == 1 ? 'checked=checked' : ''}>
									<label for="am" class="form-check-label m-2 my-auto">
										${moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a')}
									</label>
								</div>
								<div class="form-check form-check-inline removetest">
									<input type="radio" name="half_type_id" value="2/${obj.time_start_pm}/${obj.time_end_pm}" id="pm" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${window.data.hrleave.half_type_id == 2 ? 'checked=checked' : ''}>
									<label for="pm" class="form-check-label m-2 my-auto">
										${moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a')}
									</label>
								</div>
							` : ''}
						</div>
					` : ''}

				</div>
				${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
				<div class="form-group row m-2 ${errors.document ? 'has-error' : ''}">
					<label for="doc" class="col-sm-4 col-form-label">Upload Supporting Document : </label>
					<div class="col-sm-8 supportdoc">
						<input type="file" name="document" id="doc" class="form-control form-control-sm form-control-file ${errors.document ? 'is-invalid' : ''}" placeholder="Supporting Document">
					</div>
				</div>
				<div class="form-group row m-2 ${errors.documentsupport ? 'has-error' : ''}">
					<div class="offset-sm-4 col-sm-8 form-check">
						<input type="checkbox" name="documentsupport" value="1" id="suppdoc" class="form-check-input ${errors.documentsupport ? 'is-invalid' : ''}">
						<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</label>
					</div>
				</div>
			</div>
		`);

		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
					if( moment(window.data.dateTimeStartHis).isSame(moment(obj.time_start_am, 'HH:mm:ss')) ) {
						// console.log('ppagi');
						$('#am').prop('checked', true);
					} else {
						// console.log('ptg');
						$('#pm').prop('checked', true);
					}
				}
			}
		});

		if( moment(window.data.dateTimeStartHis).isSame(moment(obj.time_start_am, 'HH:mm:ss')) ) {
			// console.log('ppagi');
			$('#am').prop('checked', true);
			$('#pm').prop('checked', false);
		} else {
			// console.log('ptg');
			$('#am').prop('checked', false);
			$('#pm').prop('checked', true);
		}

		$(document).on('change', '#removeleavehalf :radio', function () {
			if (this.checked) {
				$('.removetest').remove();
			}
		});
	}
	// start date
	initDatepicker('#from').on('dp.change dp.update', function(e) {
		// $('#form').bootstrapValidator('revalidateField', 'date_time_start');
		$('#to').datetimepicker('minDate', $('#from').val());

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#from').val();

						// convert data1 into json
						// var obj = getTimeLeave(datenow);
						var obj = getTimeLeave('#from');

						var checkedam = '';
						var checkedpm = '';
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

						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
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
	// end date from

	// end date
	initDatepicker('#to').on('dp.change dp.update', function(e) {
		// $('#to').bootstrapValidator('revalidateField', 'date_time_start');
		$('#from').datetimepicker('maxDate', $('#to').val());

		if($('#from').val() === $('#to').val()) {
			if( $('.removehalfleave').length === 0) {

				////////////////////////////////////////////////////////////////////////////////////////
				// checking half day leave
				let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
				// console.log(d);
				if(d === true) {
					$('#wrapperday').append(`${leave_cat}`);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
					var datenow = $('#to').val();

					// convert data1 into json
					// var obj = getTimeLeave(datenow);
					var obj = getTimeLeave('#to');

					var checkedam = '';
					var checkedpm = '';
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

					$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

				} else {
					$('#wrapperday').append(`${leave_cat}`);
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

	// time start
	$('#start').datetimepicker({ ...config.datetimepicker,
    format: 'h:mm A',
});

	// time end
	$('#end').datetimepicker({ ...config.datetimepicker,
    format: 'h:mm A',
});

	//enable select 2 for backup
	initBackupPerson();

});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
// start here when user start to select the leave type option
$('#leave_id').on('change', function() {
	let $selection = $(this).find(':selected');
	// console.log($selection);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// annual leave & UPL
	if ($selection.val() == '1' || $selection.val() == '3') {
		$('#remove').remove();
		if($selection.val() == '3') {
			$('#wrapper').append(`
				<div id="remove">
					${from}
					${to}
					${wrapperday}
					${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
					${doc}
					${suppdoc}
				</div>
			`);
		} else {
			$('#wrapper').append(`
				<div id="remove">
					${from}
					${to}
					${wrapperday}
					${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
				</div>
			`);
		}

		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		if($selection.val() == '3') {
			$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
			$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));
		}

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		initBackupPerson();

		/////////////////////////////////////////////////////////////////////////////////////////
		// start date
		initDatepicker('#from').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			// console.log(minDaten);
			$('#to').datetimepicker('minDate', minDaten);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#from').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		initDatepicker('#to').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date
		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#from').val();

				// convert data1 into json
				var obj = getTimeLeave(datenow);

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
			if (this.checked) {
				$('.removetest').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		if( $('#from').val() == $('#to').val() ) {
			$('#form').bootstrapValidator('addField', $('#halfleave').find('[name="leave_cat"]'));
		}
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '2') {

		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">
				${from}
				${to}
				${window.data.setHalfDayMC == 1 ? wrapperday : ''}
				${(window.data.userneedbackup == 99 && window.data.backup) ? userneedbackup : ''}
				${doc}
				${suppdoc}
			</div>
		`);

		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		initBackupPerson();

		// enable datetime for the 1st one
		initDatepicker('#from').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if(window.data.setHalfDayMC == 1) {
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#from').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${toggle_time_hrleave(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
			}
		});

		initDatepicker('#to').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if(window.data.setHalfDayMC == 1) {
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =  $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
			}
		});
		// end date

		if(window.data.setHalfDayMC == 1) {
		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#to').val();

				// convert data1 into json
				var obj = getTimeLeave(datenow);

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
			});
		}
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// replacement leave

	if ($selection.val() == '4') {
		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">
			${replacementForm}
			${from}
			${to}
			${wrapperday}
				${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
			</div>
		`);

		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="id"]'));
		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));


		/////////////////////////////////////////////////////////////////////////////////////////
		// enable select2 on nrla
		$('#nrla').select2({ ...config.select2,
});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable select2
		initBackupPerson();

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		initDatepicker('#from').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			// console.log(minDaten);
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#from').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		initDatepicker('#to').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =  $('#from').val();

				// convert data1 into json
				var obj = getTimeLeave(datenow);

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		// $('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				console.log( $('#nrla option:selected').data('nrlbalance') );
				if( $('#nrla option:selected').data('nrlbalance') == 0.5 ) {

					// especially for select 2, if no select2, remove change()
					$('#nrla option:selected').prop('selected', false).change();
					// $('#nrla').val('').change();
				}
				$('.removetest').remove();
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// checking for half day click but select for 1 full day
		$('#nrla').change(function() {
			let selectedOption = $('option:selected', this);
			$('#form').bootstrapValidator('revalidateField', 'leave_id');
			var nrlbal = selectedOption.data('nrlbalance');
			if (nrlbal == 0.5) {
				// make sure from and to date got value
				$('#from').val(moment().add(3, 'days').format('YYYY-MM-DD'));
				$('#to').val(moment().add(3, 'days').format('YYYY-MM-DD'));

				$('#radio2').prop('checked', true);
				// checking so there is no double

				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#from').val();

				// convert data1 into json
				var obj = getTimeLeave(datenow);

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
				}
			} else {
				if( nrlbal != 0.5 ) {
					$('#radio1').prop('checked', true);
					$('.removetest').remove();
				}
			}
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// maternity leave
	if ($selection.val() == '7') {

		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">
				${from}
				${to}
				${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
			</div>
		`);


		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		//add bootstrapvalidator
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="leave_id"]'));
		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		initBackupPerson();

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		$('#from').datetimepicker({ ...config.datetimepicker,
    minDate: moment().format('YYYY-MM-DD'),
    disabledDates:getUnavailableDates(1),
})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDate = $('#from').val();
			$('#to').datetimepicker('minDate', moment( minDate, 'YYYY-MM-DD').add(59, 'days').format('YYYY-MM-DD') );
			$('#to').val( moment( minDate, 'YYYY-MM-DD').add(59, 'days').format('YYYY-MM-DD') );
		});

		$('#to').datetimepicker({ ...config.datetimepicker,
    minDate: moment().format('YYYY-MM-DD'),
    disabledDates:getUnavailableDates(1),
})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();

			// $('#from').datetimepicker('maxDate', moment( maxDate, 'YYYY-MM-DD').subtract(59, 'days').format('YYYY-MM-DD'));
			// $('#from').val( moment( maxDate, 'YYYY-MM-DD').subtract(59, 'days').format('YYYY-MM-DD') );
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '5' || $selection.val() == '6') {		// el-al and el-upl

		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">
				${from}
				${to}
				${wrapperday}
				${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
				${doc}
				${suppdoc}
			</div>
		`);
		/////////////////////////////////////////////////////////////////////////////////////////
		//add bootstrapvalidator
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="leave_id"]'));
		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		initBackupPerson();

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		initDatepicker('#from').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#from').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			if(window.data.userneedbackup == 1) {
			// enable backup if date from is greater or equal than today.
			// cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(`${userneedbackup}`);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					initBackupPerson();
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			}
		});

		initDatepicker('#to').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#to').val();

				var data1 = $.ajax({
					url: route.timeleave,
					type: "POST",
					data: {
							date: datenow,
							id: window.data.ownerId
					},
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
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
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

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '9') { // time off

		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">

				${from}
				${timeOffHtml}
				${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
				${doc}
				${suppdoc}
			</div>
		`);
		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		//add bootstrapvalidator
		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		initBackupPerson();

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		initDatepicker('#from').on('dp.change ', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');

			if(window.data.userneedbackup == 1) {
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(`${userneedbackup}`);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					initBackupPerson();
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// time start
		// need to get working hour for each user
		// lazy to implement this 1. :P
		// moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a')
		// moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a')
		// moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a')
		// moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a')

		$('#start').datetimepicker({ ...config.datetimepicker,
    format: 'h:mm A',
})
		.on('dp.change dp.update', function(e){
			$('#form').bootstrapValidator('revalidateField', 'time_start');
			// $('#end').datetimepicker('minDate', moment($('#start').val(), 'h:mm A'));
		});

		$('#end').datetimepicker({ ...config.datetimepicker,
    format: 'h:mm A',
})
		.on('dp.change dp.update', function(e){
			$('#form').bootstrapValidator('revalidateField', 'time_end');
			// $('#start').datetimepicker('minDate', moment($('#end').val(), 'h:mm A'));
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '11') {				// mc-upl

		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">

				${from}
				${to}
				${window.data.setHalfDayMC == 1 ? `
	<div class="form-group row m-2 ${errors.leave_cat ? 'has-error' : ''}" id="wrapperday">
		<div class="form-group col-sm-8 offset-sm-4 form-check ${errors.half_type_id ? 'has-error' : ''} removehalfleave"  id="wrappertest">
						${window.data.hrleave.period_day <= 0.5 ? `
		<div class="form-check form-check-inline removetest">
			<input type="radio" name="half_type_id" value="1/${obj.time_start_am}/${obj.time_end_am}" id="am" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}">
			<label for="am" class="form-check-label m-2 my-auto">${moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a')}</label>
		</div>
		<div class="form-check form-check-inline removetest">
			<input type="radio" name="half_type_id" value="2/${obj.time_start_pm}/${obj.time_end_pm}" id="pm" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}">
			<label for="pm" class="form-check-label m-2 my-auto">${moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a')}</label>
		</div>
						` : ''}
					</div>
				</div>
				` : ''}
				${(window.data.userneedbackup == 1 && window.data.backup) ? `
				<div id="backupwrapper">
					${userneedbackup}
				</div>
				` : ''}
${doc}
${suppdoc}
			</div>
		`);

		//add bootstrapvalidator
		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		initDatepicker('#from').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if(window.data.setHalfDayMC == 1) {
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			// for backup person based on from date
			if(window.data.userneedbackup == 1) {
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(`${userneedbackup}`);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					initBackupPerson();
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			}
		});

		initDatepicker('#to').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if(window.data.setHalfDayMC == 1) {
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		if(window.data.userneedbackup == 1) {
		initBackupPerson();
		}
		/////////////////////////////////////////////////////////////////////////////////////////
		if(window.data.setHalfDayMC == 1) {
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#to').val();

				var data1 = $.ajax({
					url: route.timeleave,
					type: "POST",
					data: {
							date: datenow,
							id: window.data.ownerId
					},
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
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
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
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// el replacement leave
	if ($selection.val() == '10') {

		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">
			${replacementForm}
			${from}
			${to}
			${wrapperday}
				${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
${doc}
${suppdoc}
			</div>
		`);

		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="leave_id"]'));
		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable select2
		$('#nrla').select2({ ...config.select2,
});

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		initBackupPerson();

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		initDatepicker('#from').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#from').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${toggle_time_hrleave(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			if(window.data.userneedbackup == 1) {
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(`${userneedbackup}`);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					initBackupPerson();
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			}
		});

		initDatepicker('#to').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#from').val();

				// convert data1 into json
				var obj = getTimeLeave(datenow);

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		// $('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				console.log( $('#nrla option:selected').data('nrlbalance') );
				if( $('#nrla option:selected').data('nrlbalance') == 0.5 ) {

					// especially for select 2, if no select2, remove change()
					$('#nrla option:selected').prop('selected', false).change();
					// $('#nrla').val('').change();
				}
				$('.removetest').remove();
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// checking for half day click but select for 1 full day
		$('#nrla').change(function() {
			selectedOption = $('option:selected', this);
			$('#form').bootstrapValidator('revalidateField', 'leave_id');
			var nrlbal = selectedOption.data('nrlbalance');
			if (nrlbal == 0.5) {
				// make sure from and to date got value
				$('#from').val(moment().add(3, 'days').format('YYYY-MM-DD'));
				$('#to').val(moment().add(3, 'days').format('YYYY-MM-DD'));

				$('#radio2').prop('checked', true);
				// checking so there is no double

				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#from').val();

				// convert data1 into json
				var obj = getTimeLeave(datenow);

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(`
	<div class="form-check form-check-inline removetest">
		<input type="radio" name="half_type_id" value="1/${obj.time_start_am}/${obj.time_end_am}" id="am" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${toggle_time_start_am} ${checkedam} ${window.data.hrleave.half_type_id == 1 ? 'checked=checked' : ''}>
		<label for="am" class="form-check-label m-2 my-auto">
			${moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a')}
		</label>
	</div>
	<div class="form-check form-check-inline removetest">
		<input type="radio" name="half_type_id" value="2/${obj.time_start_pm}/${obj.time_end_pm}" id="pm" class="form-check-input ${errors.half_type_id ? 'is-invalid' : ''}" ${toggle_time_start_pm} ${checkedpm} ${window.data.hrleave.half_type_id == 2 ? 'checked=checked' : ''}>
		<label for="pm" class="form-check-label m-2 my-auto">
			${moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a')} to ${moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a')}
		</label>
	</div>
					`);
				}
			} else {
				if( nrlbal != 0.5 ) {
					$('#radio1').prop('checked', true);
					$('.removetest').remove();
				}
			}
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// S-UPL
	if ($selection.val() == '12') {

		$('#remove').remove();
		$('#wrapper').append(`
			<div id="remove">
				${from}
				${to}
				${wrapperday}
				${(window.data.userneedbackup == 1 && window.data.backup) ? userneedbackup : ''}
${doc}
${suppdoc}
			</div>
			`);
		/////////////////////////////////////////////////////////////////////////////////////////
		// add more option
		//add bootstrapvalidator
		if(window.data.userneedbackup == 1) {
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		}
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		initBackupPerson();

		/////////////////////////////////////////////////////////////////////////////////////////
		// start date
		initDatepicker('#from').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#from'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#from').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			if(window.data.userneedbackup == 1) {
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(`${userneedbackup}`);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					initBackupPerson();
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			}
		});

		initDatepicker('#to').on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					let [d, itime_start, itime_end] = getHalfdayInfo($('#to'));
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow = $('#to').val();

						// convert data1 into json
						var obj = getTimeLeave(datenow);

						var checkedam = '';
						var checkedpm = '';
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
						$('#wrappertest').append(`${toggle_time_checkedam(obj)}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					} else {
						$('#wrapperday').append(`${leave_cat}`);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow = $('#from').val();

				var data1 = $.ajax({
					url: route.timeleave,
					type: "POST",
					data: {
							date: datenow,
							id: window.data.ownerId
					},
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
					$('#wrappertest').append(`${toggle_time_hrleave(obj)}`);
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
});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// validator
$(document).ready(function() {
	$('#form').bootstrapValidator({
		fields: {
			leave_type_id: {
				validators: {
					notEmpty: {
						message: 'Please choose'
					},
				}
			},
			reason: {
				validators: {
					notEmpty: {
						message: 'Please insert your reason'
					},
					callback: {
						message: 'The reason must be less than 200 characters long',
						callback: function(value, validator, $field) {
							var div  = $('<div/>').html(value).get(0),
							text = div.textContent || div.innerText;
							return text.length <= 200;
						},
					},
				}
			},
			akuan: {
				validators: {
					notEmpty: {
						message: 'Please click this as an acknowledgement'
					}
				}
			},
			date_time_start: {
				validators: {
					notEmpty : {
						message: 'Please insert date start'
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'The value is not a valid date. '
					},
				}
			},
			date_time_end: {
				validators: {
					notEmpty : {
						message: 'Please insert date end'
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'The value is not a valid date. '
					},
				}
			},
			time_start: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-6]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			time_end: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-6]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			id: {
				validators: {
					notEmpty: {
						message: 'Please select',
					},
				}
			},
			leave_cat: {
				validators: {
					notEmpty: {
						message: 'Please select leave category',
					},
				}
			},
			staff_id: {
				validators: {
					// notEmpty: {
					// 	message: 'Please choose'
					// }
				}
			},
			amend_note: {
				validators: {
					notEmpty: {
						message: 'Please insert note'
					}
				}
			},
			document: {
				validators: {
					file: {
						extension: 'jpeg,jpg,png,bmp,pdf,doc,docx',											// no space
						type: 'image/jpeg,image/png,image/bmp,application/pdf,application/msword',			// no space
						maxSize: 5242880,	// 5120 * 1024,
						message: 'The selected file is not valid. Please use jpeg, jpg, png, bmp, pdf or doc and the file is below than 5MB. '
					},
				}
			},
			// documentsupport: {
			// 	validators: {
			// 		notEmpty: {
			// 			message: 'Please click this as an aknowledgement.'
			// 		},
			// 	}
			// },
		}
	})
	.find('[name="reason"]')
	// .ckeditor()
	// .editor
		.on('change', function() {
			// Revalidate the bio field
		$('#form').bootstrapValidator('revalidateField', 'reason');
		// console.log($('#reason').val());
	})
	;
});

/////////////////////////////////////////////////////////////////////////////////////////
// design spec step 4: once validation passes, show a spinner in the submit button while submitting
// (the plugin's default success handler already disables the submit button)
$('#form').on('success.form.bv', function () {
	$(this).find('[type="submit"]')
		.prop('disabled', true)
		.addClass('disabled')
		.append('<span class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>');
});
