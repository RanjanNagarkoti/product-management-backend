<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Auth\Events\Registered;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RegisterUserController extends Controller
{
    /**
     * @param RegisterUserRequest $request
     * @return Response
     */
    public function __invoke(RegisterUserRequest $request): Response
    {
        $data = $request->validated();

        $user = User::create($data);

        event(new Registered($user));

        return response()->noContent();
    }
}
