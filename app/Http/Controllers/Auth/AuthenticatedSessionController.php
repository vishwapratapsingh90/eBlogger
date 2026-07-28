<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        // there is no session here in api.
        // $request->session()->regenerate();

        $user = $request->user();
        $token = $user->createToken('user-token')->plainTextToken;

        // return response()->noContent();
        return response()->json([
            'message' => 'User authenticated successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        // Auth::guard('web')->logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();

        // return response()->noContent();

        $user = $request->user();

        $user->currentAccessToken->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ]);
    }
}
