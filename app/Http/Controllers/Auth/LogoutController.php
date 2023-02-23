<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LogoutController extends Controller
{
    public function __invoke(Request $request): Response
    {
        auth()->guard('web')->logout();
        $request->user()->tokens()->delete();
        Session::flush();

        return response(['message' => 'Logged out']);
    }
}
