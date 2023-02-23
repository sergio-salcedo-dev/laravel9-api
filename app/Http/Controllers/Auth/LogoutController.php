<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserLoggedOutResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LogoutController extends Controller
{
    public function __invoke(Request $request): UserLoggedOutResource
    {
        auth()->guard('web')->logout();
        $request->user()->tokens()->delete();
        Session::flush();

        return new UserLoggedOutResource($request->user());
    }
}
