<style>
	@page {
		margin-left: 1cm;
		margin-top: 0.5cm;
		margin-right: 1cm;
		margin-bottom: 0.5cm;
		size: landscape;
		font-family: Arial, sans-serif;
		font-size: 12px;
	}

	.avoid-break {
		page-break-inside: avoid;
	}

	table,
	tr,
	td {
		border-collapse: collapse;
		height: 16px;
	}


	.table-no-border table,
	.table-no-border tr,
	.table-no-border td {
		border: none;
	}

	.table-with-border table,
	.table-with-border tr,
	.table-with-border td {
		border: 1px solid black;
	}
</style>

<body>
@if($sa)
	@foreach($sa as $v)
		<div class="avoid-break">
			<table width="100%;" class="table-no-border avoid-break">
				<tr>
					<td width="90px;">
						Staff ID / Name:
					</td>
					<td>
						{{ $v['username'] }} {{ $v['name'] }}
					</td>
					<td width="70px;">
						Department:
					</td>
					<td width="280px;">
						{{ $v['dept'] }}
					</td>
					<td width="40px;">
						Group:
					</td>
					<td width="100px;">
						{{ $v['group'] }}
					</td>
				</tr>
			</table>

			<table width="100%;" class="table-with-border avoid-break">
				<tr>
					<td align="center" width="100px;">
						Date
					</td>
					<td align="center" width="70px;">
						Day Type
					</td>
					<td align="center" width="60px;">
						Leave
					</td>
					<td align="center" width="60px;">
						In
					</td>
					<td align="center" width="60px;">
						Break
					</td>
					<td align="center" width="60px;">
						Resume
					</td>
					<td align="center" width="60px;">
						Out
					</td>
					<td align="center" width="60px;">
						Duration
					</td>
					<td align="center" width="60px;">
						Overtime
					</td>
					<td align="center" width="80px;">
						Outstation
					</td>
					<td>
						&nbsp;Remark
					</td>
					<td align="center" width="60px;">
						Exception
					</td>
				</tr>

				@foreach($v['rows'] as $v1)
				<tr>
					<td>
						&nbsp;{{ $v1['date'] }}
					</td>
					<td align="center">
						{{ $v1['dayt'] }}
					</td>
					<td align="center">
						{{ $v1['leave_pdf'] }}
					</td>
					<td align="center">
						@if($v1['in_late'])
							<b><i>{{ $v1['in'] }}</i></b>
						@else
							{{ $v1['in'] }}
						@endif
					</td>
					<td align="center">
						{{ $v1['break'] }}
					</td>
					<td align="center">
						@if($v1['resume_late'])
							<b><i>{{ $v1['resume'] }}</i></b>
						@else
							{{ $v1['resume'] }}
						@endif
					</td>
					<td align="center">
						{{ $v1['out'] }}
					</td>
					<td align="right">
						{{ $v1['duration_pdf'] }}&nbsp;
					</td>
					<td align="right">
						{{ $v1['overtime_pdf'] }}&nbsp;
					</td>
					<td>
						<div style="width: 75px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
							&nbsp;{{ $v1['outstation_pdf'] }}
						</div>
					</td>
					<td>
						<div style="width: 95%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
							&nbsp;{{ $v1['remarks_pdf'] }}
						</div>
					</td>
					<td align="center">
						{{ $v1['exception'] }}
					</td>
				</tr>
				@endforeach

				<tr>
					<td colspan="7" align="right">
						<b>TOTAL&nbsp;&nbsp;&nbsp;</b>
					</td>
					<td align="right">
						{{ $v['duration_total_pdf'] }}
					</td>
					<td align="right">
						{{ $v['overtime_total_pdf'] }}&nbsp;
					</td>
					<td colspan="3"></td>
				</tr>
			</table>

			<br />
		</div>
	@endforeach
@endif
</body>
