<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementGuideController extends Controller
{
    public function page(): Response
    {
        return Inertia::render('admin/users/guide');
    }
}
