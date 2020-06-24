<?php

/*
 * This file is part of the Jiannei/lumen-api-starter.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Repositories\Constants\ResponseConstant;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['store']]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'filled|email',
            'name' => 'required_without:email',
            'password' => 'required',
        ]);

        $credentials = request(['name', 'email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            $this->response->errorUnauthorized();
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return $this->response->created(
            [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
            ],
            '',
            ResponseConstant::SERVICE_LOGIN_SUCCESS
        );
    }

    public function show()
    {
        $user = auth()->userOrFail();

        return $this->response->success(new UserResource($user));
    }

    public function destroy()
    {
        auth()->logout();

        return $this->response->noContent('Successfully logged out');
    }

    public function update()
    {
        return $this->respondWithToken(auth()->refresh());
    }
}
