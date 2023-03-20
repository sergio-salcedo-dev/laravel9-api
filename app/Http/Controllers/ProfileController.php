<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(User $user): ProfileResource
    {
        return new ProfileResource($user);
    }
}
