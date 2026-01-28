<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesAjaxRequests;

abstract class Controller
{
    use HandlesAjaxRequests;
}
