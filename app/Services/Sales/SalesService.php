<?php
namespace App\Services\Sales;

use App\Models\Sales\Sales;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Sales module service.
 *
 * Centralizes the per-row display computation that used to live inline in the
 * blade views (M2: business logic out of blades).
 */
class SalesService
{
	/**
	 * Decorate a sales collection for the index table: precompute the sale
	 * reference, the formatted dates and the approve / send / edit button
	 * fragments the blade used to build inline with Carbon + str_pad.
	 *
	 * @param  Collection  $sales
	 * @return Collection  rows with sale_ref, date_order_fmt, delivery_fmt,
	 *                     approved_html, send_html, actions_html
	 */
	public function indexRows(Collection $sales): Collection
	{
		return $sales->map(function ($sale) {
			$sale->sale_ref = $sale->belongstosalesby->sales_by . '-' . str_pad($sale->no, 3, '0', STR_PAD_LEFT) . '/' . $sale->year;
			$sale->date_order_fmt = Carbon::parse($sale->date_order)->format('j M Y');
			$sale->delivery_fmt = Carbon::parse($sale->delivery_at)->format('j M Y');

			$sale->approved_html = is_null($sale->approved_by)
				? '<button type="button" class="btn btn-sm btn-outline-secondary sale-approve" data-id="' . $sale->id . '"><i class="fa-solid fa-signature fa-beat"></i></button>'
				: Carbon::parse($sale->approved_date)->format('j F Y');

			$sale->send_html = is_null($sale->confirm)
				? (
					is_null($sale->approved_by)
						? '<p>Please get approval before proceed</p>'
						: '<button type="button" class="btn btn-sm btn-outline-secondary sale-send" data-id="' . $sale->id . '"><i class="fa-regular fa-paper-plane fa-beat"></i></button>'
				)
				: Carbon::parse($sale->confirm_date)->format('j F Y');

			$sale->actions_html = !is_null($sale->approved_by)
				? null
				: '<div class="btn-group btn-group-sm" role="group">
						<a href="' . route('sale.edit', $sale->id) . '" class="btn btn-sm btn-outline-secondary">
							<i class="fa-regular fa-pen-to-square fa-beat"></i>
						</a>
						<button class="btn btn-sm btn-outline-secondary" data-id="' . $sale->id . '">
							<i class="fa-solid fa-trash-can fa-beat" style="color: red;"></i>
						</button>
					</div>';

			return $sale;
		});
	}
}
