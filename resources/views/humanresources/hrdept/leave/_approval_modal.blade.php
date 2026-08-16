@php
    // defensive defaults for per-row variables the partial receives
    $staff = $staff ?? $staff1 ?? null;
    $backup = $backup ?? collect();
    $amend = $amend ?? collect();
    $hrremarksattendance = $hrremarksattendance ?? collect();
    $replb = $replb ?? collect();
    $replt = $replt ?? collect();
    $upal = $upal ?? collect();
    $mcupl = $mcupl ?? collect();
    $annl = $annl ?? null;
    $mcel = $mcel ?? null;
    $matl = $matl ?? null;
    $auth = $auth ?? '';
    $deptid = $deptid ?? '';
    $me5 = $me5 ?? false;
    $me1 = $me1 ?? false;
    $firstdeptid = $firstdeptid ?? null;
    $sop = $sop ?? 'box-green';
    $leave_type = $leave_type ?? $leave_type_color ?? 'box-green';
    $backup_person = $backup_person ?? 'box-red';
    $support_doc = $support_doc ?? 'box-green';
    $attendance_percentage = $attendance_percentage ?? 'box-green';
    $errors = $errors ?? collect();
    $leave_type_code = $leave_type_code ?? $leavtype?->leave_type_code ?? $leavtype?->leave_type ?? '';

    // per-type substitutions derived from $type
    [$modal_id, $label_id, $title, $status_prefix, $radio_class, $code_input_id, $code_value] = match ($type) {
        'supervisor' => [
            'sapproval' . $approval_id,
            'suplabel' . $approval_id,
            'Supervisor Approval',
            'supstatus' . $approval_id,
            'leave_status_id' . $approval_id,
            'supcode' . $approval_id,
            (($me1 && $firstdeptid == 14) || $me5) ? $leav->verify_code : '',
        ],
        'hod' => [
            'hodapproval' . $approval_id,
            'hodlabel' . $approval_id,
            'Head of Department Approval',
            'hodstatus' . $approval_id,
            '',
            'hodcode' . $approval_id,
            $leav->verify_code,
        ],
        'dir' => [
            'dirapproval' . $approval_id,
            'dirlabel' . $approval_id,
            'Director Approval',
            'dirstatus' . $approval_id,
            '',
            'dircode' . $approval_id,
            $leav->verify_code,
        ],
        'hr' => [
            'hrapproval' . $approval_id,
            'hrlabel' . $approval_id,
            'Human Resource Department Approval',
            'hrstatus' . $approval_id,
            '',
            'hrcode' . $approval_id,
            $leav->verify_code,
        ],
        default => ['', '', '', '', '', '', ''],
    };
@endphp

<div class="modal fade" id="{{ $modal_id }}" aria-labelledby="{{ $label_id }}" aria-hidden="true">
  <!-- <div class="modal fade" id="{{ $modal_id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="{{ $label_id }}">{{ $title }}</h1>
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
                <div class="table-cell" style="width: 50%;"><span id="left-detail">NAME</span>:<span id="right-detail">{{ $staff?->name }}</span></div>
              </div>
            </div>

            <div class="table">
              <div class="table-row">
                <div class="table-cell-top" style="width: 50%;"><span id="left-detail">LEAVE NO</span>:<span id="right-detail">HR9-{{ @str_pad($leav->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $leav->leave_year }}</span></div>
                <div class="table-cell-top text-wrap" style="width: 50%;"><span id="left-detail">LEAVE TYPE</span>:<span id="right-detail">{{ $leave_type_code }} ({{ $leavtype?->leave_type }})</span></div>
              </div>
            </div>

            <div class="table">
              <div class="table-row">
                <div class="table-cell-top" style="width: 50%;"><span id="left-detail">DATE CREATE | DATE LEAVE</span>:<span id="right-detail">({{ \Carbon\Carbon::parse($leav->created_at ?? now())->format('d-m-Y') }}) {{ $dts }} - {{ $dte }}</span></div>
                <div class="table-cell-top" style="width: 50%;"><span id="left-detail">TOTAL</span>:<span id="right-detail">{{ $dper }}</span></div>
              </div>
            </div>

            <div class="table">
              <div class="table-row">
                <div class="table-cell-top text-wrap" style="width: 50%;"><span id="left-detail">BACKUP</span>:<span id="right-detail">{!! $bapp !!}</span></div>
                <div class="table-cell-top" style="width: 50%;">
                  <span id="left-detail">BACKUP DATE APPROVED</span>:<span id="right-detail">{{ ($backup->first()?->created_at)?\Carbon\Carbon::parse($backup->first()?->created_at)->format('j M Y'):null }}</span>
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
                  <span id="left-detail">Entitlement Year {{ \Carbon\Carbon::parse($leav->date_time_start)->format('Y') }}</span>
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

        <form method="POST" action="{{ $route_name }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form" data-id="{{ $approval_id }}" enctype="multipart/form-data">
          @csrf
          @method('PATCH')
          <input type="hidden" name="id" value="{{ $approval_id }}">

          <div class="offset-sm-4 col-sm-6">
            @foreach($ls as $k => $val)
            <div class="form-check form-check-inline {{ $errors->has('leave_status_id') ? 'has-error' : '' }}">
              <input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="{{ $status_prefix . $val['id'] }}" class="form-check-input {{ $radio_class }}">
              <label class="form-check-label" for="{{ $status_prefix . $val['id'] }}">{{ $val['text'] }}</label>
            </div>
            @endforeach
          </div>

          <div class="form-group mb-3 row {{ $errors->has('verify_code') ? 'has-error' : '' }}">
            <label for="{{ $code_input_id }}" class="col-sm-4 col-form-label col-form-label-sm">Verify Code :</label>
            <div class="col-sm-8">
              <input type="text" name="verify_code" value="{{ $code_value }}" id="{{ $code_input_id }}" class="form-control form-control-sm" placeholder="Verify Code">
            </div>
          </div>

          <div class="form-group row mb-3 {{ $errors->has('remarks') ? 'has-error' : '' }}">
            <label for="remarks{{ $approval_id }}" class="col-sm-4 col-form-label col-form-label-sm">Remarks :</label>
            <div class="col-sm-8">
              <textarea name="remarks" value="{{ $leav->remarks }}" id="remarks{{ $approval_id }}" class="form-control form-control-sm" rows="3" placeholder="Remarks"></textarea>
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
</div>