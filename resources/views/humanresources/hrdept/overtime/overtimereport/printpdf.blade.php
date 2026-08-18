<style>
  .theme,
  .theme tr,
  .theme td {
    border-collapse: collapse;
    width: 100%;
    font-size: 20px;
    font-family: 'Arial', sans-serif;
  }

  .table,
  .table tr,
  .table td {
    border: 1px solid black;
    font-size: 10px;
    border-collapse: collapse;
    width: 100%;
  }

  .table td {
    height: 18px;
  }

  .top-row td {
    background-color: #cccccc;
  }

  .overflow {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
  }

  .footer,
  .footer tr,
  .footer td {
    font-size: 11px;
    border-collapse: collapse;
    width: 100%;
  }

  @page {
    margin: 0.7cm;
    size: A4 landscape;
  }

.remark,
.remark tr,
.remark td {
  border-collapse: collapse;
    width: 100%;
    font-size: 12px;
    font-family: 'Arial', sans-serif;
}
</style>
<table class="theme">
  <tr>
    <td align="center">
      {{ $report['claim_form_title'] }} ({{ $title }} of {{ $month }} {{ $year }}) </td>
  </tr>
</table>

<table height="15px"></table>

<table class="table">
  <tr class="top-row">
    <td align="center" style="width: 20px;">
      NO
    </td>
    <td align="center" style="width: 40px;">
      ID
    </td>
    <td align="center">
      NAME
    </td>
    <td align="center" style="width: 70px;">
      DEPT
    </td>	@foreach ($report['columns'] as $column)
    <td align="center" style="width: 30px;">
      {{ $column['label'] }}
    </td>
    @endforeach
    <td align="center" style="width: 50px;">
      TOTAL<br />HOURS
    </td>
    <td align="center" style="width: 60px;">
      SIGNATURE
    </td>
  </tr>	@foreach ($report['rows'] as $index => $overtime)
  <tr>
    <td align="center">
      {{ $index + 1 }}
    </td>
    <td align="center">
      {{ $overtime['username'] }}
    </td>
    <td>
      <div class="overflow" style="max-width: 250px;">
        &nbsp;{{ $overtime['name'] }}
      </div>
    </td>
    <td>
      <div class="overflow" style="width: 65px">
        &nbsp;{{ $overtime['department'] }}
      </div>
    </td>
    @foreach ($overtime['cells'] as $cell)
    <td align="center" style="{{ $cell['background'] }}">
      {{ $cell['time'] }}
    </td>
    @endforeach
    <td align="right">
      {{ $overtime['total'] }}
      &nbsp;
    </td>
    <td></td>
  </tr>
  @endforeach

  <tr>
    <td align="right" colspan="{{ $report['total_col'] + 4 }}">
      TOTAL HOURS&nbsp;&nbsp;
    </td>
    <td align="right">
      {{ $report['grand_total'] }}
      &nbsp;
    </td>
    <td></td>
  </tr>
</table>

<table style="height: 2px;"></table>

<table class="remark">
  <tr>
    <td style="width:24px">
      <div style="width: 16px; height: 16px; background-color: #d9d9d9;"></div>
    </td>
    <td>
      REMARK
    </td>
  </tr>
</table>

<table style="height: 30px;"></table>

<table class="footer">
  <tr>
    <td align="center" style="width: 33%">
      ____________________________________________<br />SUBMITTED BY
    </td>
    <td align="center" style="width: 33%">
      ____________________________________________<br />CHECKED BY PRODUCTION SUPERVISOR
    </td>
    <td align="center" style="width: 33%">
      ____________________________________________<br />APPROVED BY
    </td>
  </tr>
</table>