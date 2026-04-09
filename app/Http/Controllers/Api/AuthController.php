<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        /** @var User $user */
        $user = $request->user();
        $user->loadMissing('agent', 'employee');

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'agent_id' => $user->agent_id,
                'employee_id' => $user->employee_id,
            ],
            'agent' => $user->agent,
            'employee' => $user->employee,
        ]);
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('agent', 'employee');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'agent_id' => $user->agent_id,
                'employee_id' => $user->employee_id,
            ],
            'agent' => $user->agent,
            'employee' => $user->employee,
        ]);
    }

    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}

