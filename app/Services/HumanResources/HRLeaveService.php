<?php
namespace App\Services\HumanResources;

// for controller output
use Illuminate\Http\{
	JsonResponse,
	RedirectResponse,
	// Response
};
// use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
// use Illuminate\Support\Facades\Http;

// models
use App\Models\{
	HumanResources\HRLeave,
};

// load request
use Illuminate\Http\Request;

// load db facade
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\DB;

// load batch and queue
// use Illuminate\Bus\Batch;
// use Illuminate\Support\Facades\Bus;

// load email & notification
// use Illuminate\Support\Facades\{
// 	Mail, Notification
// };

// load helper
use Illuminate\Support\{
	Arr, Str, Collection, Facades\Storage
};

// load Carbon library
use \Carbon\{
	Carbon, CarbonPeriod, CarbonInterval
};

// use Session;
// use Throwable;
// use Exception;
// use Log;

class HRLeaveService extends Controller
{
	public function __construct()
	{
	}

	//
}
