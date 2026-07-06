<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SupportCenterController extends Controller
{
    public function page(): Response
    {
        return Inertia::render('admin/support/index');
    }
}
