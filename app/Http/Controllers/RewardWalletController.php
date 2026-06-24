<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MissionFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardWalletController extends Controller
{
    public function __invoke(Request $request, MissionFlowService $service): JsonResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return response()->json([
            'status' => 'success',
            'data' => $service->walletForUser($user),
        ]);
    }
}
