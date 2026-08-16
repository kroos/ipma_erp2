<!-- Hidden ID for edit mode -->
<input type="hidden" name="id" value="{{ old('id', $var->id) }}">

<!-- Text Input -->
<div class="form-group row m-2 @error('column_name') has-error @enderror">
	<label for="column_name" class="col-sm-4 col-form-label col-form-label-sm">Column Name : </label>
	<div class="col-sm-8 my-auto">
		<input
			type="text"
			name="column_name"
			id="column_name"
			value="{{ old('column_name', $var->column_name) }}"
			class="form-control form-control-sm @error('column_name') is-invalid @enderror"
			placeholder="Column Name"
		>
		@error('column_name')
			<div class="invalid-feedback fw-lighter">{{ $message }}</div>
		@enderror
	</div>
</div>

<!-- Select Dropdown -->
<div class="form-group row m-2 @error('option_id') has-error @enderror">
	<label for="option_id" class="col-sm-4 col-form-label col-form-label-sm">Option : </label>
	<div class="col-sm-8 my-auto">
		<select
			name="option_id"
			id="option_id"
			class="form-select form-select-sm @error('option_id') is-invalid @enderror"
		>
			<option value="">Please choose</option>
			<option value="1" {{ old('option_id', $var->option_id) == 1 ? 'selected' : '' }}>Option 1</option>
			<option value="2" {{ old('option_id', $var->option_id) == 2 ? 'selected' : '' }}>Option 2</option>
		</select>
		@error('option_id')
			<div class="invalid-feedback fw-lighter">{{ $message }}</div>
		@enderror
	</div>
</div>

<!-- Textarea -->
<div class="form-group row m-2 @error('description') has-error @enderror">
	<label for="description" class="col-sm-4 col-form-label col-form-label-sm">Description : </label>
	<div class="col-sm-8 my-auto">
		<textarea
			name="description"
			id="description"
			rows="3"
			class="form-control form-control-sm @error('description') is-invalid @enderror"
			placeholder="Description"
		>{{ old('description', $var->description) }}</textarea>
		@error('description')
			<div class="invalid-feedback fw-lighter">{{ $message }}</div>
		@enderror
	</div>
</div>

<!-- Date & Time (Bootstrap datetimepicker) -->
<div class="form-group row m-2 @error('date_column') has-error @enderror">
	<label for="date_column" class="col-sm-4 col-form-label col-form-label-sm">Date : </label>
	<div class="col-sm-8 my-auto">
		<input
			type="text"
			name="date_column"
			id="date_column"
			value="{{ old('date_column', $var->date_column) }}"
			class="form-control form-control-sm @error('date_column') is-invalid @enderror"
			placeholder="YYYY-MM-DD"
		>
		@error('date_column')
			<div class="invalid-feedback fw-lighter">{{ $message }}</div>
		@enderror
	</div>
</div>

<!-- Rich Text (tinyMCE) -->
<div class="form-group row m-2 @error('content') has-error @enderror">
	<label for="content" class="col-sm-4 col-form-label col-form-label-sm">Content : </label>
	<div class="col-sm-8 my-auto">
		<textarea
			name="content"
			id="content"
			rows="10"
			class="form-control form-control-sm tinymce-editor @error('content') is-invalid @enderror"
			placeholder="Content"
		>{{ old('content', $var->content) }}</textarea>
		@error('content')
			<div class="invalid-feedback fw-lighter">{{ $message }}</div>
		@enderror
	</div>
</div>

<!-- Switch / Toggle -->
<div class="form-group row m-2">
	<label for="is_active" class="col-sm-4 col-form-label col-form-label-sm">Active : </label>
	<div class="col-sm-8 my-auto">
		<div class="form-check form-switch">
			<input
				type="checkbox"
				name="is_active"
				id="is_active"
				value="1"
				class="form-check-input @error('is_active') is-invalid @enderror"
				role="switch"
				{{ old('is_active', $var->is_active) ? 'checked' : '' }}
			>
			@error('is_active')
				<div class="invalid-feedback fw-lighter">{{ $message }}</div>
			@enderror
		</div>
	</div>
</div>

<!-- Select2 AJAX (commented as example) -->
{{-- 
<div class="form-group row m-2 @error('relation_id') has-error @enderror">
	<label for="relation_id" class="col-sm-4 col-form-label col-form-label-sm">Related Item : </label>
	<div class="col-sm-8 my-auto">
		<select
			name="relation_id"
			id="relation_id"
			class="form-select form-select-sm @error('relation_id') is-invalid @enderror"
		></select>
		@error('relation_id')
			<div class="invalid-feedback fw-lighter">{{ $message }}</div>
		@enderror
	</div>
</div>
--}}

<!-- Dynamic Rows (commented as example) -->
{{-- 
<div id="items_wrap" class="row @error('items') is-invalid @enderror"></div>
@error('items')
	<div class="invalid-feedback">{{ $message }}</div>
@enderror
<div class="col-12-sm">
	<button type="button" id="items_add" class="m-1 btn btn-sm btn-outline-primary">+ Add Item</button>
</div>
--}}
