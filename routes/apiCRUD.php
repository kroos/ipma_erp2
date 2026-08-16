<?php
/*
|--------------------------------------------------------------------------
| API CRUD Routes (write endpoints only)
|--------------------------------------------------------------------------
|
| This file holds WRITE ajax endpoints only. All endpoints here must:
| - Resolve to a method on `App\Http\Controllers\API\ModelAjaxCRUDController`
| - Use POST / PUT / PATCH / DELETE only
| - Return { status, message, ... } JSON shape
|
| READ endpoints (DataTables, Select2, autocomplete) live in routes/api.php
| and route to `App\Http\Controllers\API\AjaxSupportController`.
|
| See AGENTS.md §4 for the rule.
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ModelAjaxCRUDController;

Route::middleware(['auth'])->prefix('api')->group(function () {

	/* ------- sales write endpoints (migrated from routes/Sales/ajax_sales.php) ------- */
	Route::patch('/saleamend/{saleamend}', [ModelAjaxCRUDController::class, 'saleamend'])->name('saleamend');
	Route::patch('/saleapproved/{saleapproved}', [ModelAjaxCRUDController::class, 'saleapproved'])->name('saleapproved');
	Route::patch('/salesend/{salesend}', [ModelAjaxCRUDController::class, 'salesend'])->name('salesend');

	/* ------- hr write endpoints (migrated from routes/HumanResources/ajax_hr.php) ------- */
	Route::patch('/leavecancel/{hrleave}', [ModelAjaxCRUDController::class, 'leavecancel'])->name('leavecancel.leavecancel');
	Route::patch('/uploaddoc/{hrleave}', [ModelAjaxCRUDController::class, 'uploaddoc'])->name('uploaddoc')->middleware('highMgmtAccess:1|5,14');
	Route::patch('/leaverapprove/{hrleaveapprovalbackup}', [ModelAjaxCRUDController::class, 'leaverapprove'])->name('leaverapprove.leaverapprove');
	Route::patch('/supervisorstatus', [ModelAjaxCRUDController::class, 'supervisorstatus'])->name('leavestatus.supervisorstatus');
	Route::patch('/hodstatus', [ModelAjaxCRUDController::class, 'hodstatus'])->name('leavestatus.hodstatus');
	Route::patch('/dirstatus', [ModelAjaxCRUDController::class, 'dirstatus'])->name('leavestatus.dirstatus');
	Route::patch('/hrstatus', [ModelAjaxCRUDController::class, 'hrstatus'])->name('leavestatus.hrstatus');
	Route::patch('/deactivatestaff/{staff}', [ModelAjaxCRUDController::class, 'deactivatestaff'])->name('deactivatestaff')->middleware('highMgmtAccess:1|5,14');
	Route::delete('/deletecrossbackup/{staff}', [ModelAjaxCRUDController::class, 'deletecrossbackup'])->name('deletecrossbackup')->middleware('highMgmtAccess:1|5,14');
	Route::patch('/staffactivate/{staff}', [ModelAjaxCRUDController::class, 'staffactivate'])->name('staff.activate')->middleware('highMgmtAccess:1|5,14');
	Route::post('/generateannualleave', [ModelAjaxCRUDController::class, 'generateannualleave'])->name('generateannualleave')->middleware('highMgmtAccess:1|5,14');
	Route::post('/generatemcleave', [ModelAjaxCRUDController::class, 'generatemcleave'])->name('generatemcleave')->middleware('highMgmtAccess:1|5,14');
	Route::post('/generatematernityleave', [ModelAjaxCRUDController::class, 'generatematernityleave'])->name('generatematernityleave')->middleware('highMgmtAccess:1|5,14');
	Route::post('/confirmoutstationattendance', [ModelAjaxCRUDController::class, 'confirmoutstationattendance'])->name('confirmoutstationattendance');

});
