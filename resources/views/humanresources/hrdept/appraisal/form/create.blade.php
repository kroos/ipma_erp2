@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-appraisal-form-create container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form : {{ $category->category }}</h4>

  <table height="15px"></table>

  <form method="POST" action="{{ route('appraisalform.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
    @csrf

  <input type="hidden" name="category_id" value="{{ $category->id }}">

  <div class="row">
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 1</b>
      <img src="{{ asset('images/appraisal/Bahagian1.jpg') }}" width="620px">
    </div>
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 2</b>
      <img src="{{ asset('images/appraisal/Bahagian2.jpg') }}" width="620px">
    </div>
  </div>


  <div class="row">
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 3</b>
      <img src="{{ asset('images/appraisal/Bahagian3.jpg') }}" width="620px">
    </div>
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 4</b>
      <img src="{{ asset('images/appraisal/Bahagian4.jpg') }}" width="620px">
    </div>
  </div>


  <div class="row mb-3">
    <div style="width: 4%">
      <button type="button" class="col-auto btn btn-sm btn-outline-secondary p1_add">
        <i class="fas fa-plus" aria-hidden="true"></i><br />P1
      </button>
    </div>
    <div class="p1_wrap" style="width: 96%">
      <!-- JAVASCRIPT -->
    </div>
  </div>

  <div class="d-flex justify-content-center m-3">
    <button type="submit" class="btn btn-sm btn-outline-secondary">SUBMIT</button>
	</div>

  </form>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      <a href="{{ url()->previous() }}">
        <button class="btn btn-sm btn-outline-secondary">BACK</button>
      </a>
    </div>
  </div>

</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
		appraisalformupdate: '{{ url('appraisalform/update') }}',
	},
	old: {
	},
	editId: @json(isset($id) ? $id : null),
	err: {
		p1sort: @json($errors->has('p1.*.section_sort') ? 'has-error' : ''),
		p1text: @json($errors->has('p1.*.section_text') ? 'has-error' : ''),
		p2sort: @json($errors->has('p2.*.sectionsub_sort') ? 'has-error' : ''),
		p2text: @json($errors->has('p2.*.sectionsub_text') ? 'has-error' : ''),
		p3mainsort: @json($errors->has('p3.*.mainquestion_sort') ? 'has-error' : ''),
		p3mainmark: @json($errors->has('p3.*.mainquestion_mark') ? 'has-error' : ''),
		p3maintext: @json($errors->has('p3.*.mainquestion_text') ? 'has-error' : ''),
		p4qsort: @json($errors->has('p4.*.question_sort') ? 'has-error' : ''),
		p4qmark: @json($errors->has('p4.*.question_mark') ? 'has-error' : ''),
		p4qtext: @json($errors->has('p4.*.question_text') ? 'has-error' : ''),
	},
	errors: @json($errors->toArray()),
};
@endsection
