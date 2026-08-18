@extends('layouts.app')

@section('content')
<div class="page-humanresources-leave-show col-sm-12 row">
  @include('humanresources.hrdept.navhr')
  <h4>Leave Application</h4>

  <div class="table-container">
    <div class="table">
      <div class="table-row header">
        <div class="table-cell" style="width: 40%; background-color: #99ff99;">IPMA INDUSTRY SDN.BHD.</div>
        <div class="table-cell" style="width: 40%; background-color: #e6e6e6;">LEAVE APPLICATION FORM</div>
        <div class="table-cell" style="{{ $leave_color }}">{{ $leave_status }}</div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top" style="width: 25%;">STAFF ID : {{ $username }}</div>
        <div class="table-cell-top" style="width: 75%;">NAME : {{ $staff_name }}</div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top" style="width: 25%;">LEAVE NO : {{ $leave_ref }}</div>
        <div class="table-cell-top" style="width: 60%;">DATE : {{ $date_start }} - {{ $date_end }} </div>
        <div class="table-cell-top" style="width: 25%;">TOTAL : {{ $total_leave }} </div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top text-wrap" style="width: 45%;">LEAVE TYPE : {{ @$leave->belongstooptleavetype->leave_type_code }} ({{ @$leave->belongstooptleavetype->leave_type }})</div>
        <div class="table-cell-top text-wrap" style="width: 55%;">REASON : {{ @$leave->reason }} </div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top text-wrap" style="width: 60%;">BACKUP : {{ $backup_name }}</div>
        <div class="table-cell-top" style="width: 40%;">BACKUP APPROVED : {{ $approved_date }} </div>
      </div>
    </div>

    @if ($canViewAttendance)
    @if($hrremarksattendance)
    <div class="table">
      @foreach($hrremarksattendance as $key => $value)
      <div class="table-row">
        <div class="table-cell-top" style="width: 100%;">ATTENDANCE REMARK : {{ $value->remarks }}<br />HR ATTENDANCE REMARK : {{ $value->hr_remarks }}</div>
      </div>
      @endforeach
    </div>
    @endif
    @endif

    @if ($canViewAttendance)
    @if($leave->remarks)
    <div class="table">
      <div class="table-row">
        <div class="table-cell-top" style="width: 100%;">LEAVE REMARK : {{ $leave->remarks }}</div>
      </div>
    </div>
    @endif
    @endif

    @if ($canViewAttendance)
    @if($amend_notes->count())
    <div class="table">
      @foreach($amend_notes as $key => $value1)
      <div class="table-row">
        <div class="table-cell-top" style="width: 100%;">EDIT REMARK : {{ $value1->amend_note }} on {{ $value1->created_fmt }}</div>
      </div>
      @endforeach
    </div>
    @endif
    @endif

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top text-center" style="width: 100%; background-color: #ffcc99; font-size: 18px;">SIGNATURE / APPROVAL</div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        @foreach ($approvals as $approval)
      <div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">{{ $approval->label }}</div>
      @endforeach
    </div>
  </div>

  <div class="table">
    <div class="table-row" style="height: 40px;">
      @foreach ($approvals as $approval)
          <div class="table-cell-top-bottom text-center text-decoration-underline text-wrap text-uppercase" style="width: {{ $width }}%; vertical-align: bottom;">
            {{ $approval->name }}
          </div>
      @endforeach
    </div>
  
    <div class="table-row">
      @foreach ($approvals as $approval)
          <div class="table-cell-top1 text-center">
            {{ $approval->updated_at }}<br />
            <span style="{{ $approval->color }}">{{ $approval->status }}</span>
          </div>
      @endforeach
    </div>
  </div>

  <div class="table">
    <div class="table-row">
      Supporting Document : @if($leave->softcopy)<a href="{{ asset('storage/leaves/'.$leave->softcopy) }}" target="_blank">Link</a>@endif
    </div>
  </div>

  <div class="table" style="height: 10px;">
    <div class="table-row"></div>
  </div>

  <div class="table">
    <div class="table-row">
      <div class="table-cell-hidden text-center" style="width: 100%;">
        <a href="{{ url()->previous() }}"><button class="btn btn-sm btn-outline-secondary" id="back">Back</button></a>
        <a href=""><button onclick="printPage()" class="btn btn-sm btn-outline-secondary" id="printPageButton">Print</button></a>
      </div>
    </div>
  </div>

</div>
@endsection

@section('js')

@endsection