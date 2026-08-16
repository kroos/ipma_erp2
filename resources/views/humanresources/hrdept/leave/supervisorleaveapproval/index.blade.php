@extends('layouts.app')

@section('content')
<?php
// M2 W5: model imports moved to LeaveApprovalService; keep aliases used by markup
use Illuminate\Support\Str;
use Carbon\Carbon;
?>
<div class="page-humanresources-hrdept-leave-supervisorleaveapproval-index container row align-items-start justify-content-center">
  @include('humanresources.hrdept.navhr')
  @if($s1)
  @if($approvals->count())
  <div class="col-sm-12 table-responsive">
    <h4>Supervisor Approval</h4>
    <table class="table table-hover table-sm" id="sapprover" style="font-size:12px">
      <thead>
        <tr>
          <th rowspan="2">ID Leave</th>
          <th rowspan="2">ID</th>
          <th rowspan="2">Name</th>
          <th rowspan="2">Leave</th>
          <th rowspan="2">Reason</th>
          <th rowspan="2">Date Applied</th>
          <th colspan="2">Date/Time Leave</th>
          <th rowspan="2">Period</th>
          <th rowspan="2">Backup Status</th>
          <th rowspan="2">Approval</th>
        </tr>
        <tr>
          <th>From</th>
          <th>To</th>
        </tr>
      </thead>
      <tbody>
        @foreach($approvals as $a)
        @php extract($grid[$a->id]); @endphp

        @if($me3)
        @if($ul == $us)
        <tr class="{{ $u }}">
          <td>
            <a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
          </td>
          <td>{{ $username }}</td>
          <td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff1->name }}">
            {{ Str::words($staff1?->name, 3, ' >') }}
          </td>
          <td>{{ $leave_type_code }}</td>
          <td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
            {{ Str::limit($leav->reason, 7, ' >') }}
          </td>
          <td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
          <td>{{ $dts }}</td>
          <td>{{ $dte }}</td>
          <td>{{ $dper }}</td>
          <td>{!! $bapp !!}</td>
          <td>
            <!-- Button trigger modal -->
            @if($backup->count())
            @if(!is_null($backup->first()->leave_status_id))
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
            @endif
            @else
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
            @endif

            <!-- Modal for supervisor approval-->
            <div class="modal fade" id="sapproval{{ $a->id }}" aria-labelledby="suplabel{{ $a->id }}" aria-hidden="true">
              <!-- <div class="modal fade" id="sapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
              <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="suplabel{{ $a->id }}">Supervisor Approval</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body align-items-start justify-content-center">

                    <!-------------------------------------------------------------------------------- LEAVE SHOW START -------------------------------------------------------------------------------->
                    <div class="col-sm-12 row">
                      <div class="table-container">
                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $sop }}'></div><span id="left-detail">According SOP</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $leave_type }}'></div><span id="left-detail">Leave Type</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $backup_person }}'></div><span id="left-detail">Backup Person</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $support_doc }}'></div><span id="left-detail">Supporting Doc</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $attendance_percentage }}'></div><span id="left-detail">Attendance Above 80%</span>
                            </div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell" style="width: 50%;"><span id="left-detail">STAFF ID</span>:<span id="right-detail">{{ $username }}</span></div>
                            <div class="table-cell" style="width: 50%;"><span id="left-detail">NAME</span>:<span id="right-detail">{{ $staff1?->name }}</span></div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 50%;"><span id="left-detail">LEAVE NO</span>:<span id="right-detail">HR9-{{ @str_pad($leav->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $leav->leave_year }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 50%;"><span id="left-detail">LEAVE TYPE</span>:<span id="right-detail">{{ $leave_type_code }} ({{ $leavtype->leave_type }})</span></div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 50%;"><span id="left-detail">DATE CREATE | DATE LEAVE</span>:<span id="right-detail">({{ Carbon::parse($a->created_at)->format('d-m-Y') }}) {{ $dts }} - {{ $dte }}</span></div>
                            <div class="table-cell-top" style="width: 50%;"><span id="left-detail">TOTAL</span>:<span id="right-detail">{{ $dper }}</span></div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top text-wrap" style="width: 50%;"><span id="left-detail">BACKUP</span>:<span id="right-detail">{!! $bapp !!}</span></div>
                            <div class="table-cell-top" style="width: 50%;">
                              <span id="left-detail">BACKUP DATE APPROVED</span>:<span id="right-detail">{{ ($backup->first()?->created_at)?Carbon::parse($backup->first()?->created_at)->format('j M Y'):null }}</span>
                            </div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top text-wrap" style="width: 100%;"><span id="left-detail">REASON</span>:<span id="right-detail">{{ $leav->reason }}</span></div>
                          </div>
                        </div>

                        @if ((in_array($auth, ['1', '2', '5']) && in_array($deptid, ['14', '31'])) || $me5)
                        @if($leav->remarks)
                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 100%;"><span id="left-detail">LEAVE REMARKS</span>:<span id="right-detail">{{ $leav->remarks }}</span></div>
                          </div>
                        </div>
                        @endif
                        @endif

                        @if ((in_array($auth, ['1', '2', '5']) && in_array($deptid, ['14', '31'])) || $me5)
                        @if($amend->count())
                        <div class="table">
                          @foreach($amend as $key => $value1)
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 100%;"><span id="left-detail">EDIT LEAVE REMARKS</span>:<span id="right-detail">{{ $value1->amend_note }} on {{ \Carbon\Carbon::parse($value1->created_at)->format('d-m-Y') }}</span></div>
                          </div>
                          @endforeach
                        </div>
                        @endif
                        @endif

                        @if ((in_array($auth, ['1', '2', '5']) && in_array($deptid, ['14', '31'])) || $me5)
                        @if($hrremarksattendance)
                        <div class="table">
                          @foreach($hrremarksattendance as $key => $value)
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 100%;"><span id="left-detail">REMARKS FROM ATTENDANCE</span>:<span id="right-detail">{{ $value->remarks }}</span><br /><span id="left-detail">HR REMARKS FROM ATTENDANCE</span>:<span id="right-detail">{{ $value->hr_remarks }}</span></div>
                          </div>
                          @endforeach
                        </div>
                        @endif
                        @endif

                        <p>Supporting Document : @if($leav->softcopy)<a href="{{ asset('storage/leaves/'.$leav->softcopy) }}" target="_blank">Link</a>@endif </p>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell">
                              <span id="left-detail">Entitlement Year {{ Carbon::parse($leav->date_time_start)->format('Y') }}</span>
                            </div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">AL</span>:<span id="right-detail">{{ $annl?->annual_leave_balance }}/{{ $annl?->annual_leave + $annl?->annual_leave_adjustment }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">MC</span>:<span id="right-detail">{{ $mcel?->mc_leave_balance }}/{{ $mcel?->mc_leave + $mcel?->mc_leave_adjustment }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 16%;"><span id="left-detail">Maternity</span>:<span id="right-detail">{{ $matl?->maternity_leave_balance }}/{{ $matl?->maternity_leave + $matl?->maternity_leave_adjustment }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">Replacement</span>:<span id="right-detail">{{ $replb?->first()?->total }}/{{ $replt?->first()?->total }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">UPL</span>:<span id="right-detail">{{ $upal?->first()?->total }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 16%;"><span id="left-detail">MC-UPL</span>:<span id="right-detail">{{ $mcupl?->first()?->total }}</span></div>
                          </div>
                        </div>

                        <p></p>

                      </div>
                    </div>
                    <!-------------------------------------------------------------------------------- LEAVE SHOW END -------------------------------------------------------------------------------->

                    <form method="POST" action="{{ route('leavestatus.supervisorstatus') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form" data-id="{{ $a->id }}" enctype="multipart/form-data">
                      @csrf
                      @method('PATCH')
                    <input type="hidden" name="id" value="{{ $a->id }}">

                    <div class="offset-sm-4 col-sm-6">
                      @foreach($ls as $k => $val)
                      <div class="form-check form-check-inline">
                        <input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="supstatus{{ $a->id.$val['id'] }}" class="form-check-input">
                        <label class="form-check-label" for="supstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
                      </div>
                      @endforeach
                    </div>

                    <div class="form-group row mb-3 {{ $errors->has('verify_code') ? 'has-error' : '' }}">
                      <label for="supcode{{ $a->id }}" class="col-sm-4 col-form-label col-form-label-sm">Verify Code :</label>
                      <div class="col-sm-8">
                        <input type="text" name="verify_code" value="{{ (($me1 && $firstdeptid == 14) || $me5)?$leav->verify_code:@$value }}" id="supcode{{ $a->id }}" class="form-control form-control-sm" placeholder="Verify Code">
                      </div>
                    </div>

                    <div class="form-group row mb-3 {{ $errors->has('remarks') ? 'has-error' : '' }}">
                      <label for="remarks{{ $a->id }}" class="col-sm-4 col-form-label col-form-label-sm">Remarks :</label>
                      <div class="col-sm-8">
                        <textarea name="remarks" value="{{ $a->remarks }}" id="remarks{{ $a->id }}" class="form-control form-control-sm" rows="3" placeholder="Remarks"></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
                  </div>
                    </form>
                </div>
              </div>
            </div>

          </td>
        </tr>
        @endif
        @else
        <tr class="{{ $u }}">
          <td>
            <a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
          </td>
          <td>{{ $username }}</td>
          <td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff1->name }}">
            {{ Str::words($staff1?->name, 3, ' >') }}
          </td>
          <td>{{ $leave_type_code }}</td>
          <td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
            {{ Str::limit($leav->reason, 7, ' >') }}
          </td>
          <td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
          <td>{{ $dts }}</td>
          <td>{{ $dte }}</td>
          <td>{{ $dper }}</td>
          <td>{!! $bapp !!}</td>
          <td>
            <!-- Button trigger modal -->
            @if($backup->count())
            @if(!is_null($backup->first()->leave_status_id))
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
            @endif
            @else
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
            @endif

            <!-- Modal for supervisor approval-->
            <div class="modal fade" id="sapproval{{ $a->id }}" aria-labelledby="suplabel{{ $a->id }}" aria-hidden="true">
              <!-- <div class="modal fade" id="sapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
              <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="suplabel{{ $a->id }}">Supervisor Approval</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body align-items-start justify-content-center">

                    <!-------------------------------------------------------------------------------- LEAVE SHOW START -------------------------------------------------------------------------------->
                    <div class="col-sm-12 row">
                      <div class="table-container">
                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $sop }}'></div><span id="left-detail">According SOP</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $leave_type }}'></div><span id="left-detail">Leave Type</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $backup_person }}'></div><span id="left-detail">Backup Person</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $support_doc }}'></div><span id="left-detail">Supporting Doc</span>
                            </div>
                            <div class="table-cell-none" width="20%">
                              <div id='{{ $attendance_percentage }}'></div><span id="left-detail">Attendance Above 80%</span>
                            </div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell" style="width: 50%;"><span id="left-detail">STAFF ID</span>:<span id="right-detail">{{ $username }}</span></div>
                            <div class="table-cell" style="width: 50%;"><span id="left-detail">NAME</span>:<span id="right-detail">{{ $staff1?->name }}</span></div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 50%;"><span id="left-detail">LEAVE NO</span>:<span id="right-detail">HR9-{{ @str_pad($leav->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $leav->leave_year }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 50%;"><span id="left-detail">LEAVE TYPE</span>:<span id="right-detail">{{ $leave_type_code }} ({{ $leavtype->leave_type }})</span></div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 50%;"><span id="left-detail">DATE CREATE | DATE LEAVE</span>:<span id="right-detail">({{ Carbon::parse($a->created_at)->format('d-m-Y') }}) {{ $dts }} - {{ $dte }}</span></div>
                            <div class="table-cell-top" style="width: 50%;"><span id="left-detail">TOTAL</span>:<span id="right-detail">{{ $dper }}</span></div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top text-wrap" style="width: 50%;"><span id="left-detail">BACKUP</span>:<span id="right-detail">{!! $bapp !!}</span></div>
                            <div class="table-cell-top" style="width: 50%;">
                              <span id="left-detail">BACKUP DATE APPROVED</span>:<span id="right-detail">{{ ($backup->first()?->created_at)?Carbon::parse($backup->first()?->created_at)->format('j M Y'):null }}</span>
                            </div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top text-wrap" style="width: 100%;"><span id="left-detail">REASON</span>:<span id="right-detail">{{ $leav->reason }}</span></div>
                          </div>
                        </div>

                        @if ((in_array($auth, ['1', '2', '5']) && in_array($deptid, ['14', '31'])) || $me5)
                        @if($leav->remarks)
                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 100%;"><span id="left-detail">LEAVE REMARKS</span>:<span id="right-detail">{{ $leav->remarks }}</span></div>
                          </div>
                        </div>
                        @endif
                        @endif

                        @if ((in_array($auth, ['1', '2', '5']) && in_array($deptid, ['14', '31'])) || $me5)
                        @if($amend->count())
                        <div class="table">
                          @foreach($amend as $key => $value1)
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 100%;"><span id="left-detail">EDIT LEAVE REMARKS</span>:<span id="right-detail">{{ $value1->amend_note }} on {{ \Carbon\Carbon::parse($value1->created_at)->format('d-m-Y') }}</span></div>
                          </div>
                          @endforeach
                        </div>
                        @endif
                        @endif

                        @if ((in_array($auth, ['1', '2', '5']) && in_array($deptid, ['14', '31'])) || $me5)
                        @if($hrremarksattendance)
                        <div class="table">
                          @foreach($hrremarksattendance as $key => $value)
                          <div class="table-row">
                            <div class="table-cell-top" style="width: 100%;"><span id="left-detail">REMARKS FROM ATTENDANCE</span>:<span id="right-detail">{{ $value->remarks }}</span><br /><span id="left-detail">HR REMARKS FROM ATTENDANCE</span>:<span id="right-detail">{{ $value->hr_remarks }}</span></div>
                          </div>
                          @endforeach
                        </div>
                        @endif
                        @endif

                        <p>Supporting Document : @if($leav->softcopy)<a href="{{ asset('storage/leaves/'.$leav->softcopy) }}" target="_blank">Link</a>@endif </p>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell">
                              <span id="left-detail">Entitlement Year {{ Carbon::parse($leav->date_time_start)->format('Y') }}</span>
                            </div>
                          </div>
                        </div>

                        <div class="table">
                          <div class="table-row">
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">AL</span>:<span id="right-detail">{{ $annl?->annual_leave_balance }}/{{ $annl?->annual_leave + $annl?->annual_leave_adjustment }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">MC</span>:<span id="right-detail">{{ $mcel?->mc_leave_balance }}/{{ $mcel?->mc_leave + $mcel?->mc_leave_adjustment }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 16%;"><span id="left-detail">Maternity</span>:<span id="right-detail">{{ $matl?->maternity_leave_balance }}/{{ $matl?->maternity_leave + $matl?->maternity_leave_adjustment }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">Replacement</span>:<span id="right-detail">{{ $replb?->first()?->total }}/{{ $replt?->first()?->total }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 17%;"><span id="left-detail">UPL</span>:<span id="right-detail">{{ $upal?->first()?->total }}</span></div>
                            <div class="table-cell-top text-wrap" style="width: 16%;"><span id="left-detail">MC-UPL</span>:<span id="right-detail">{{ $mcupl?->first()?->total }}</span></div>
                          </div>
                        </div>

                        <p></p>

                      </div>
                    </div>
                    <!-------------------------------------------------------------------------------- LEAVE SHOW END -------------------------------------------------------------------------------->

                    <form method="POST" action="{{ route('leavestatus.supervisorstatus') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form" data-id="{{ $a->id }}" enctype="multipart/form-data">
                      @csrf
                      @method('PATCH')
                    <input type="hidden" name="id" value="{{ $a->id }}">

                    <div class="offset-sm-4 col-sm-6">
                      @foreach($ls as $k => $val)
                      <div class="form-check form-check-inline">
                        <input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="supstatus{{ $a->id.$val['id'] }}" class="form-check-input leave_status_id{{ $a->id }}">
                        <label class="form-check-label" for="supstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
                      </div>
                      @endforeach
                    </div>

                    <div class="form-group row mb-3 {{ $errors->has('verify_code') ? 'has-error' : '' }}">
                      <label for="supcode{{ $a->id }}" class="col-sm-4 col-form-label col-form-label-sm">Verify Code :</label>
                      <div class="col-sm-8">
                        <input type="text" name="verify_code" value="{{ (($me1 && $firstdeptid == 14) || $me5)?$leav->verify_code:@$value }}" id="supcode{{ $a->id }}" class="form-control form-control-sm" placeholder="Verify Code">
                      </div>
                    </div>

                    <div class="form-group row mb-3 {{ $errors->has('remarks') ? 'has-error' : '' }}">
                      <label for="remarks{{ $a->id }}" class="col-sm-4 col-form-label col-form-label-sm">Remarks :</label>
                      <div class="col-sm-8">
                        <textarea name="remarks" value="{{ $a->remarks }}" id="remarks{{ $a->id }}" class="form-control form-control-sm" rows="3" placeholder="Remarks"></textarea>
                      </div>
                    </div>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
                  </div>
                    </form>
                </div>
              </div>
            </div>

          </td>
        </tr>
        @endif
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
  @endif
</div>
@endsection


@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// form submit via ajax
$(document).on('submit', '.form', function (e) {
    e.preventDefault();

    let form = $(this);
    let ids  = form.data('id');

    $.ajax({
        url: form.attr('action'),
        type: 'PATCH',
        data: {
            _token: '{{ csrf_token() }}',
            id: ids,
            leave_status_id: form.find('input[name="leave_status_id"]:checked').val(),
            verify_code: form.find('#supcode' + ids).val(),
            remarks: form.find('#remarks' + ids).val()
        },
        dataType: 'json',
        success: function (response) {
            $('#sapproval' + ids).modal('hide');

            // remove row
            form.closest('tr').remove();

            swal.fire('Success!', response.message, 'success');
        },
        error: function (resp) {
            let res = resp.responseJSON ?? { message: 'Unknown error' };
            swal.fire('Error!', res.message, 'error');
        }
    });
});

/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});


/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#bapprover, #sapprover, #hodapprover, #dirapprover, #hrapprover').DataTable({
	paging: false,
	// lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	columnDefs: [ { type: 'date', targets: [5,6,7] } ],
	order: [[6, "desc" ]],	// sorting the 4th column descending
	responsive: true
})

.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
@endsection

@section('nonjquery')
@endsection
