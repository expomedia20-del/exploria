<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupportKnowledgeBaseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportCenterController extends Controller
{
    public function page(Request $request, SupportKnowledgeBaseService $knowledgeBase): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return Inertia::render('admin/support/index', [
            'support' => $knowledgeBase->forUser($user),
        ]);
    }
}
