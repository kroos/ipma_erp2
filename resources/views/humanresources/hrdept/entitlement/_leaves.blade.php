@if($leaves->count())
	<table class="table table-hover table-sm">
		<thead>
			<tr>
				<th>Leave ID</th>
				<th>Duration</th>
			</tr>
		</thead>
		<tbody>
			@foreach($leaves as $leave)
				<tr>
					<td>
						<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad($leave->leave_no, 5, '0', STR_PAD_LEFT) }}/{{ $leave->leave_year }}</a>
					</td>
					<td>{{ $leave->period_day }} day/s</td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<th>Total</th>
				<th>{{ $total }} day/s</th>
			</tr>
		</tfoot>
	</table>
@endif
