<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\LoginUserRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class LoginUserController extends Controller
{
    /**
     * @param LoginUserRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function __invoke(LoginUserRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = auth()->user();

        $token = $user->createToken('user_token')->plainTextToken;

        return response()->json([
            'message' => "Successfully logged In!",
            'token' => $token
        ]);
    }
}
