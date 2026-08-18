<style>
	.table,
	.table tr,
	.table td {
		border: 1px solid black;
		font-size: 9px;
		border-collapse: collapse;
		width: 100%;
		font-family: 'Arial', sans-serif;
	}

	.table td {
		height: 18px;
	}

	.top-row td {
		background-color: #cccccc;
	}

	.text-center {
		text-align: center;
	}

	.DEPARTMENT {
		white-space: nowrap;
		width: 65px;
		overflow: hidden;
	}

	.NAME {
		white-space: nowrap;
		width: 145px;
		overflow: hidden;
	}

	.REMARK {
		white-space: nowrap;
		width: 95%;
		overflow: hidden;
	}

	@page {
		margin: 0.30cm;
	}
</style>


<span style="font-size:18px;">DAILY ATTENDANCE</span>

@if ($dailyreport_absent->isNotEmpty() || $lateRows->isNotEmpty() || $dailyreport_outstation->isNotEmpty())
<table class="table">
	<!-- ABSENT -->
	@if ($dailyreport_absent->isNotEmpty())
	<?php $no = 1; ?>
	<tr class="top-row">
		<td colspan="11">
			<b>ABSENT</b>
		</td>
	</tr>
	<tr class="top-row">
		<td class="text-center" style="width: 20px;">
			NO
		</td>
		<td class="text-center" style="width: 55px;">
			DATE
		</td>
		<td class="text-center" style="width: 70px;">
			STATUS
		</td>
		<td class="text-center" style="width: 50px;">
			LOCATION
		</td>
		<td class="text-center" style="width: 70px;">
			DEPARTMENT
		</td>
		<td class="text-center" style="width: 45px;">
			GROUP
		</td>
		<td class="text-center" style="width: 40px;">
			ID
		</td>
		<td class="text-center" style="width: 150px;">
			NAME
		</td>
		<td colspan="2" class="text-center">
			REASON / REMARK
		</td>
		<td class="text-center" style="width: 65px;">
			LEAVE ID
		</td>
	</tr>

	@foreach ($dailyreport_absent as $absent)

	<tr>
		<td class="text-center">
			{{ $no++ }}
		</td>
		<td class="text-center">
			{{ $absent->attend_date }}
		</td>
		<td class="text-center">
			{{ $absent->status }}
		</td>
		<td class="text-center">
			{{ $absent->code }}
		</td>
		<td>
			<div class="DEPARTMENT">
				&nbsp;{{ $absent->department }}
			</div>
		</td>
		<td class="text-center">
			{{ $absent->group }}
		</td>
		<td class="text-center">
			{{ $absent->username }}
		</td>
		<td>
			<div class="NAME">
				&nbsp;{{ $absent->name }}
			</div>
		</td>
		<td colspan="2">
			<div class="REMARK">
				&nbsp;{{ $absent->remark }}
			</div>
		</td>
		<td class="text-center">
			@if ($absent->leave_number != NULL)
			{{ $absent->leave_number }}
			@endif
		</td>
	</tr>
	@endforeach
	@endif


	<!-- LATE -->
	@if ($lateRows->isNotEmpty())
	<?php $no = 1; ?>
	<tr class="top-row">
		<td colspan="11">
			<b>LATE</b>
		</td>
	</tr>
	<tr class="top-row">
		<td class="text-center">
			NO
		</td>
		<td class="text-center">
			DATE
		</td>
		<td class="text-center">
			STATUS
		</td>
		<td class="text-center">
			LOCATION
		</td>
		<td class="text-center">
			DEPARTMENT
		</td>
		<td class="text-center">
			GROUP
		</td>
		<td class="text-center">
			ID
		</td>
		<td class="text-center">
			NAME
		</td>
		<td class="text-center">
			REASON / REMARK
		</td>
		<td class="text-center" style="width: 45px;">
			IN
		</td>
		<td class="text-center">
			LEAVE ID
		</td>
	</tr>

	@foreach ($lateRows as $late)

	<tr>
		<td class="text-center">
			{{ $no++ }}
		</td>
		<td class="text-center">
			{{ $late->attend_date }}
		</td>
		<td class="text-center">
			LATE
		</td>
		<td class="text-center">
			{{ $late->code }}
		</td>
		<td>
			<div class="DEPARTMENT">
				&nbsp;{{ $late->department }}
			</div>
		</td>
		<td class="text-center">
			{{ $late->group }}
		</td>
		<td class="text-center">
			{{ $late->username }}
		</td>
		<td>
			<div class="NAME">
				&nbsp;{{ $late->name }}
			</div>
		</td>
		<td>
			<div class="REMARK">
				&nbsp;{{ $late->remark }}
			</div>
		</td>
		<td class="text-center">
			<span class="text-danger">{{ $late->in }}</span>
		</td>
		<td class="text-center">
			@if ($late->leave_number != NULL)
			{{ $late->leave_number }}
			@endif
		</td>
	</tr>
@endforeach
	@endif


	<!-- OUTSTATION -->
	@if ($dailyreport_outstation->isNotEmpty())
	<?php $no = 1; ?>
	<tr class="top-row">
		<td colspan="11">
			<b>OUTSTATION</b>
		</td>
	</tr>
	<tr class="top-row">
		<td class="text-center">
			NO
		</td>
		<td class="text-center">
			DATE
		</td>
		<td class="text-center">
			STATUS
		</td>
		<td class="text-center">
			LOCATION
		</td>
		<td class="text-center">
			DEPARTMENT
		</td>
		<td class="text-center">
			GROUP
		</td>
		<td class="text-center">
			ID
		</td>
		<td class="text-center">
			NAME
		</td>
		<td colspan="2" class="text-center">
			REASON / REMARK
		</td>
		<td class="text-center">
			LEAVE ID
		</td>
	</tr>

	@foreach ($dailyreport_outstation as $outstation)

	<tr>
		<td class="text-center">
			{{ $no++ }}
		</td>
		<td class="text-center">
			{{ $outstation->attend_date }}
		</td>
		<td class="text-center">
			{{ $outstation->status }}
		</td>
		<td class="text-center">
			{{ $outstation->code }}
		</td>
		<td>
			<div class="DEPARTMENT">
				&nbsp;{{ $outstation->department }}
			</div>
		</td>
		<td class="text-center">
			{{ $outstation->group }}
		</td>
		<td class="text-center">
			{{ $outstation->username }}
		</td>
		<td>
			<div class="NAME">
				&nbsp;{{ $outstation->name }}
			</div>
		</td>
		<td colspan="2">
			<div class="REMARK">
				&nbsp;{{ $outstation->remark }}
			</div>
		</td>
		<td class="text-center">
			{{ $outstation->contact }}
		</td>
	</tr>
@endforeach
	@endif

</table>
@endif
