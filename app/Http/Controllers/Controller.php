<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Page;
use App\Models\Translation;

class Controller extends BaseController
{
    protected $DEBUG_MODE = false;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
