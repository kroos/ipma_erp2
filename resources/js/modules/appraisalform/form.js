const { url, editId, err } = window.data;

const isEdit = !!editId;

// CKEDITOR toolbar config (identical on the create and edit pages)
const appraisalToolbar = [
	{ name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
	{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
	{ name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
	{ name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
];

if (isEdit) {
	/////////////////////////////////////////////////////////////////////////////////////////
	// CKEDITOR
	$(".myButton").on('click', function (e) {

		e.preventDefault();
		var Id = $(this).data('id');
		var Name = $(this).data('name');
		var editor = Name + Id;

		CKEDITOR.replace(editor, appraisalToolbar);
	});


	CKEDITOR.replace('section_text_add', appraisalToolbar);


	/////////////////////////////////////////////////////////////////////////////////////////
	// ADD AJAX SECTION
	$(".form_section_add").on('submit', function (e) {

		var editor = CKEDITOR.instances['section_text_add'];
		var section_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				add: 'P1',
				sort: $('#section_sort_add').val(),
				section: section_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#section_add').modal('hide');
				// var row = $('#section_add').parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#section_add').modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});


	/////////////////////////////////////////////////////////////////////////////////////////
	// ADD AJAX SECTION SUB
	$(".form_section_sub_add").on('submit', function (e) {

		var ids = $(this).data('id');
		var editor = CKEDITOR.instances['section_sub_text_add' + ids];
		var section_sub_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				add: 'P2',
				id: ids,
				sort: $('#section_sub_sort_add' + ids).val(),
				section_sub: section_sub_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#section_sub_add' + ids).modal('hide');
				// var row = $('#section_sub_add' + ids).parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#section_sub_add' + ids).modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});


	/////////////////////////////////////////////////////////////////////////////////////////
	// ADD AJAX MAIN QUESTION
	$(".form_main_question_add").on('submit', function (e) {

		var ids = $(this).data('id');
		var editor = CKEDITOR.instances['main_question_text_add' + ids];
		var main_question_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				add: 'P3',
				id: ids,
				mark: $('#main_question_mark_add' + ids).val(),
				sort: $('#main_question_sort_add' + ids).val(),
				main_question: main_question_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#main_question_add' + ids).modal('hide');
				// var row = $('#main_question_add' + ids).parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#main_question_add' + ids).modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});


	/////////////////////////////////////////////////////////////////////////////////////////
	// ADD AJAX QUESTION
	$(".form_question_add").on('submit', function (e) {

		var ids = $(this).data('id');
		var editor = CKEDITOR.instances['question_text_add' + ids];
		var question_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				add: 'P4',
				id: ids,
				mark: $('#question_mark_add' + ids).val(),
				sort: $('#question_sort_add' + ids).val(),
				question: question_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#question_add' + ids).modal('hide');
				// var row = $('#question_add' + ids).parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#question_add' + ids).modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});


	/////////////////////////////////////////////////////////////////////////////////////////
	// EDIT AJAX SECTION
	$(".form_section").on('submit', function (e) {

		var ids = $(this).data('id');
		var editor = CKEDITOR.instances['section_text' + ids];
		var section_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				update: 'section',
				id: ids,
				sort: $('#section_sort' + ids).val(),
				section: section_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#section' + ids).modal('hide');
				// var row = $('#section' + ids).parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#section' + ids).modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});


	/////////////////////////////////////////////////////////////////////////////////////////
	// EDIT AJAX SECTION SUB
	$(".form_section_sub").on('submit', function (e) {

		var ids = $(this).data('id');
		var editor = CKEDITOR.instances['section_sub_text' + ids];
		var section_sub_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				update: 'section_sub',
				id: ids,
				sort: $('#section_sub_sort' + ids).val(),
				section_sub: section_sub_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#section_sub' + ids).modal('hide');
				// var row = $('#section_sub' + ids).parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#section_sub' + ids).modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});


	/////////////////////////////////////////////////////////////////////////////////////////
	// EDIT AJAX MAIN QUESTION
	$(".form_main_question").on('submit', function (e) {

		var ids = $(this).data('id');
		var editor = CKEDITOR.instances['main_question_text' + ids];
		var main_question_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				update: 'main_question',
				id: ids,
				sort: $('#main_question_sort' + ids).val(),
				mark: $('#main_question_mark' + ids).val(),
				main_question: main_question_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#main_question' + ids).modal('hide');
				// var row = $('#main_question' + ids).parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#main_question' + ids).modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});


	/////////////////////////////////////////////////////////////////////////////////////////
	// EDIT AJAX QUESTION
	$(".form_question").on('submit', function (e) {

		var ids = $(this).data('id');
		var editor = CKEDITOR.instances['question_text' + ids];
		var question_text = editor.getData();

		e.preventDefault();
		$.ajax({
			url: url.appraisalformupdate,
			type: 'PATCH',
			data: {
				update: 'question',
				id: ids,
				sort: $('#question_sort' + ids).val(),
				mark: $('#question_mark' + ids).val(),
				question: question_text
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				$('#question' + ids).modal('hide');
				// var row = $('#question' + ids).parent().parent();
				// row.remove();
				swal.fire({ ...config.swal,
    title: 'Success!',
    text: response.message,
    icon: response.status,
}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function (resp) {
				const res = resp.responseJSON;
				$('#question' + ids).modal('hide');
				swal.fire('Error!', res.message, 'error');
			}
		});
	});
} else {
	/////////////////////////////////////////////////////////////////////////////////////
	// p1
	var num = 0;
	var p1_num = 0;
	var p2_num = 0;
	var p3_num = 0;
	var p4_num = 0;
	var p1_add = $(".p1_add");
	var p1_wrap = $(".p1_wrap");

	$(p1_add).click(function(){
		num++;
		p1_num++;

		p1_wrap.append(
			'<div class="section">' +
				'<input type="hidden" name="p1_end" value="'+p1_num+'">' +
				'<div class="row mb-1">' +
					'<div class="col-sm-1">' +
						'<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_section" data-id="'+num+'">' +
							'<i class="fas fa-trash" aria-hidden="true"></i>' +
						'</button>' +
					'</div>' +
					'<div class="col-sm-1 ' + err.p1sort + '">' +
						'<input type="number" name="p1'+p1_num+'['+num+'][section_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
					'</div>' +
				'</div>' +
				'<div class="mb-1 ' + err.p1text + '">' +
					'<textarea id="editor'+num+'" name="p1'+p1_num+'['+num+'][section_text]"></textarea>' +
				'</div>' +
				'<div class="row mb-5">' +
					'<div style="width: 4%">' +
						'<button type="button" class="col-auto btn btn-sm btn-outline-secondary p2_add'+num+'" data-id="'+p1_num+'">' +
							'<i class="fas fa-plus" aria-hidden="true"></i><br />P2' +
						'</button>' +
					'</div>' +
					'<div class="p2_wrap'+num+'" style="width: 96%">' +
					'</div>' +
				'</div>' +
			'</div>'
		);

		CKEDITOR.replace('editor'+num, appraisalToolbar);

		$('#form').bootstrapValidator('addField', $('.section').find('[name="p1'+p1_num+'['+num+'][section_sort]"]'));
		$('#form').bootstrapValidator('addField', $('.section').find('[name="p1'+p1_num+'['+num+'][section_text]"]'));

		$(p1_wrap).on("click",".remove_section", function(e){
			var sectionId = $(this).data('id');
			e.preventDefault();
			var $row = $(this).parent().parent().parent();
			var $option1 = $row.find('[name="p1'+p1_num+'['+sectionId+'][section_sort]"]');
			var $option2 = $row.find('[name="p1'+p1_num+'['+sectionId+'][section_text]"]');
			var $option3 = $row.find('[name="p1_end"]');
			$row.remove();

			$('#form').bootstrapValidator('removeField', $option1);
			$('#form').bootstrapValidator('removeField', $option2);
			console.log(num);
		});


		/////////////////////////////////////////////////////////////////////////////////////
		// p2
		var p2_add = $(".p2_add"+num);
		var p2_wrap = $(".p2_wrap"+num);

		$(p2_add).click(function(){
			num++;
			p2_num++;
			var p1_end = $(this).data('id');

			p2_wrap.append(
				'<div class="sectionsub">' +
					'<input type="hidden" name="p2_end" value="'+p2_num+'">' +
					'<div class="row mb-1">' +
						'<div class="col-sm-1">' +
							'<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_sectionsub" data-id="'+num+'">' +
								'<i class="fas fa-trash" aria-hidden="true"></i>' +
							'</button>' +
						'</div>' +
						'<div class="col-sm-1 ' + err.p2sort + '">' +
							'<input type="number" name="p2'+p1_end+p2_num+'['+num+'][sectionsub_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
						'</div>' +
					'</div>' +
					'<div class="mb-1 ' + err.p2text + '">' +
						'<textarea id="editor'+num+'" name="p2'+p1_end+p2_num+'['+num+'][sectionsub_text]"></textarea>' +
					'</div>' +
					'<div class="row mb-1">' +
						'<div style="width: 4%">' +
							'<button type="button" class="col-auto btn btn-sm btn-outline-secondary p3_add'+num+'" data-id="'+p2_num+'">' +
								'<i class="fas fa-plus" aria-hidden="true"></i><br />P3' +
							'</button>' +
						'</div>' +
						'<div class="p3_wrap'+num+'" style="width: 96%">' +
						'</div>' +
					'</div>' +
				'</div>'
			);

			CKEDITOR.replace('editor'+num, appraisalToolbar);

			$('#form').bootstrapValidator('addField', $('.sectionsub').find('[name="p2'+p1_end+p2_num+'['+num+'][sectionsub_sort]"]'));
			$('#form').bootstrapValidator('addField', $('.sectionsub').find('[name="p2'+p1_end+p2_num+'['+num+'][sectionsub_text]"]'));

			$(p2_wrap).on("click",".remove_sectionsub", function(e){
				var sectionsubId = $(this).data('id');
				e.preventDefault();
				var $row = $(this).parent().parent().parent();
				var $option1 = $row.find('[name="p2'+p1_end+p2_num+'['+sectionsubId+'][sectionsub_sort]"]');
				var $option2 = $row.find('[name="p2'+p1_end+p2_num+'['+sectionsubId+'][sectionsub_text]"]');
				var $option3 = $row.find('[name="p2_end"]');
				$row.remove();

				$('#form').bootstrapValidator('removeField', $option1);
				$('#form').bootstrapValidator('removeField', $option2);
				console.log(num);
			});


			/////////////////////////////////////////////////////////////////////////////////////
			// p3
			var p3_add = $(".p3_add"+num);
			var p3_wrap = $(".p3_wrap"+num);

			$(p3_add).click(function(){
				num++;
				p3_num++;
				var p2_end = $(this).data('id');

				p3_wrap.append(
					'<div class="mainquestion">' +
						'<input type="hidden" name="p3_end" value="'+p3_num+'">' +
						'<div class="row mb-1">' +
							'<div class="col-sm-1">' +
								'<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_mainquestion" data-id="'+num+'">' +
									'<i class="fas fa-trash" aria-hidden="true"></i>' +
								'</button>' +
							'</div>' +
							'<div class="col-sm-1 ' + err.p3mainsort + '">' +
								'<input type="number" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
							'</div>' +
							'<div class="col-sm-1 ' + err.p3mainmark + '">' +
								'<input type="number" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_mark]" class="form-control form-control-sm" placeholder="Mark" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
							'</div>' +
						'</div>' +
						'<div class="mb-1 ' + err.p3maintext + '">' +
							'<textarea id="editor'+num+'" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_text]"></textarea>' +
						'</div>' +
						'<div class="row mb-1">' +
							'<div style="width: 4%">' +
								'<button type="button" class="col-auto btn btn-sm btn-outline-secondary p4_add'+num+'" data-id="'+p3_num+'">' +
									'<i class="fas fa-plus" aria-hidden="true"></i><br />P4' +
								'</button>' +
							'</div>' +
							'<div class="p4_wrap'+num+'" style="width: 96%">' +
							'</div>' +
						'</div>' +
					'</div>'
				);

				CKEDITOR.replace('editor'+num, appraisalToolbar);

				$('#form').bootstrapValidator('addField', $('.mainquestion').find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_sort]"]'));
				$('#form').bootstrapValidator('addField', $('.mainquestion').find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_mark]"]'));
				$('#form').bootstrapValidator('addField', $('.mainquestion').find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_text]"]'));

				$(p3_wrap).on("click",".remove_mainquestion", function(e){
					var mainquestionId = $(this).data('id');
					e.preventDefault();
					var $row = $(this).parent().parent().parent();
					var $option1 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_sort]"]');
					var $option2 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_mark]"]');
					var $option3 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_text]"]');
					var $option4 = $row.find('[name="p3_end"]');
					$row.remove();

					$('#form').bootstrapValidator('removeField', $option1);
					$('#form').bootstrapValidator('removeField', $option2);
					$('#form').bootstrapValidator('removeField', $option3);
					console.log(num);
				});


				/////////////////////////////////////////////////////////////////////////////////////
				// p4
				var p4_add = $(".p4_add"+num);
				var p4_wrap = $(".p4_wrap"+num);

				$(p4_add).click(function(){
					num++;
					p4_num++;
					var p3_end = $(this).data('id');

					p4_wrap.append(
						'<div class="question">' +
							'<input type="hidden" name="p4_end" value="'+p4_num+'">' +
							'<div class="row mb-1">' +
								'<div class="col-sm-1">' +
									'<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_question" data-id="'+num+'">' +
										'<i class="fas fa-trash" aria-hidden="true"></i>' +
									'</button>' +
								'</div>' +
								'<div class="col-sm-1 ' + err.p4qsort + '">' +
									'<input type="number" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
								'</div>' +
								'<div class="col-sm-1 ' + err.p4qmark + '">' +
									'<input type="number" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_mark]" class="form-control form-control-sm" placeholder="Mark" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
								'</div>' +
							'</div>' +
							'<div class="mb-1 ' + err.p4qtext + '">' +
								'<textarea id="editor'+num+'" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_text]"></textarea>' +
							'</div>' +
						'</div>'
					);

					CKEDITOR.replace('editor'+num, appraisalToolbar);

					$('#form').bootstrapValidator('addField', $('.question').find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_sort]"]'));
					$('#form').bootstrapValidator('addField', $('.question').find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_mark]"]'));
					$('#form').bootstrapValidator('addField', $('.question').find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_text]"]'));

					$(p4_wrap).on("click",".remove_question", function(e){
						var questionId = $(this).data('id');
						e.preventDefault();
						var $row = $(this).parent().parent().parent();
						var $option1 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_sort]"]');
						var $option2 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_mark]"]');
						var $option3 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_text]"]');
						var $option4 = $row.find('[name="p4_end"]');
						$row.remove();

						$('#form').bootstrapValidator('removeField', $option1);
						$('#form').bootstrapValidator('removeField', $option2);
						$('#form').bootstrapValidator('removeField', $option3);
						console.log(num);
					});
				})
			})
		})
	})
}
