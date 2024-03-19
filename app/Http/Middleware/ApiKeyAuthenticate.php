<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ApiKeyAuthenticate
{
    public function handle($request, Closure $next)
    {
        $accessKey = $request->header('Key'); // Assuming the access key is included in the request headers

        if (!$accessKey) {
            return response()->json(['status' => false, 'message' => 'Invalid api key'], 403);
        }

        // Check if the access key exists in the users table
        $user = \App\Models\User::where('api_key', $accessKey)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Invalid api key'], 403);
        }

        if ($user) {
            Auth::login($user); // Perform automatic login for the user
            //            \auth('api')->login($user);
        }

        return $next($request);
    }
}
