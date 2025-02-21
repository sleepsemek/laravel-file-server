<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => $validator->errors()->first(),
                ],
            ], 422);
        }

        if ($this->attemptLogin($request)) {
            $user = Auth::user();
            $token = $user->createToken('mobile-app-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ], 200);
        }

        return response()->json([
            'error' => [
                'code' => 422,
                'message' => 'Неверные учетные данные',
            ],
        ], 422);
    }

}
