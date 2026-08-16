@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-leave-hrleaveapproval-index container row align-items-start justify-content-center">
  @include('humanresources.hrdept.navhr')
  @if($r1)
  @if($approvals->count())
  <div class="col-sm-12 table-responsive">
    <h4>Human Resource Approval</h4>
    <table class="table table-hover table-sm" id="leaveapproval-hr" style="font-size:12px">
      <thead>
        <tr>
          <th rowspan="2">Leave ID</th>
          <th rowspan="2">ID</th>
          <th rowspan="2">Name</th>
          <th rowspan="2">Leave</th>
          <th rowspan="2">Reason</th>
          <th rowspan="2">Date Applied</th>
          <th colspan="2">Date/Time Leave</th>
          <th rowspan="2">Period</th>
          <th rowspan="2">Backup Status</th>
          <th rowspan="2">Supervisor Status</th>
          <th rowspan="2">HOD Status</th>
          <th rowspan="2">Director Status</th>
          <th rowspan="2">Approval</th>
        </tr>
        <tr>
          <th>From</th>
          <th>To</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
  @endif
  @endif
</div>
@endsection

@section('js')
window.data = { route: { patch: '{{ route('leavestatus.hrstatus') }}' }, url: { table: '{{ route('api.leaveapproval.table', 'hr') }}' }, type: 'hr', old: {}, errors: @json($errors->toArray()) };
@endsection