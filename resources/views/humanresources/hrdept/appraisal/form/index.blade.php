@extends('layouts.app')

@section('content')

<div class="page-humanresources-hrdept-appraisal-form-index container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form</h4>

  <div class="row">&nbsp;</div>

  @foreach ($categories as $category)

  <div class="row mb-2" style="background-color: #f0f0f0; font-size: 20px;">
    <div class="col-sm-12 ">
      <a class="btn btn-primary btn-sm" href="{{ route('appraisalform.create', ['id' => $category->category->id] ) }}" role="button">+</a>
      {{ $category->category->category }}
    </div>
  </div>

  @foreach ($category->form_versions as $form_version)
  @if ($form_version->version != NULL)
  <div class="row mb-2">
    <div align="right" style="width: 75px;">
      <i class="bi bi-caret-right-fill"></i>
    </div>
    <div class="col-sm-9" style="font-size: 18px;">
      {{ $category->category->category }} Version {{ $form_version->version }}
    </div>
    <div align="center" style="width: 60px;">
      <a href="{{ route('appraisalform.show', ['appraisalform' => $form_version->id]) }}">
        <button type="submit" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-file-text" aria-hidden="true"></i>
        </button>
      </a>
    </div>
    <div align="center" style="width: 60px;">
      <a href="{{ route('appraisalform.edit', ['appraisalform' => $form_version->id]) }}">
        <button type="submit" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-pencil" aria-hidden="true"></i>
        </button>
      </a>
    </div>
    <div align="center" style="width: 60px;">
      <button type="button" class="btn btn-sm btn-outline-secondary appraisal_duplicate" data-id="{{ $form_version->id }}">
        <i class="fas fa-clone" aria-hidden="true"></i>
      </button>
    </div>
    <div align="center" style="width: 60px;">
      <button type="button" class="btn btn-sm btn-outline-secondary appraisal_delete" data-id="{{ $form_version->id }}">
        <i class="fas fa-trash" aria-hidden="true"></i>
      </button>
    </div>
  </div>
  @endif
  @endforeach
  @endforeach

</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
		appraisalformduplicatestore: '{{ url('appraisalformduplicate/store') }}',
		appraisalform: '{{ url('appraisalform') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection