<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\API\BaseApiController;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Str;

class AuthApiController extends BaseApiController
{

    /**
     * Check if the provided email is available.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmailAvailability(Request $request)
    {
        $email = $request->email;
        try {
            // Validate the input parameter
            $request->validate([
                'email' => 'required|email',
            ]);
        } catch (ValidationException $e) {
            // If validation fails, return validation errors
            return response()->json([
                'status' => false,
                'message' => "Validation failed!",
                'errors' => $e->errors()
            ], 200);
        }
        // Check if email is available
        $emailAvailable = !User::where('email', $request->email)->exists();

        return response()->json([
            'status' => $emailAvailable,
            'message' => $emailAvailable ? "Email available" : "Email $email is unavailable",
        ], 200);
    }
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signup(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'business_name' => 'required|string|min:3',
            'business_type' => 'required|string|min:3',
            'phone' => 'required|string|unique:users,phone',
        ]);

        // If validation fails, return validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validation failed!",
                'errors' => $validator->errors()
            ], 200);
        }

        // Create and save the new user
        $user = new User($validator->validated());
        $user->id = Str::uuid();
        $user->password = bcrypt($request->password);
        $user->api_key = Str::uuid();
        $user->status = 'active';
        $user->subscription_plan = 'free';
        $user->subscription_expiry = now()->addYear();
        $user->save();

        // Issue token for the newly created user
        $token = $user->createToken('App');

        // Return success response with token
        return response()->json([
            'status' => true,
            'message' => "User registered successfully.",
            'data' => [
                'user' => new UserResource($user),
                'token' => [
                    'type' => "Bearer",
                    'access_token' => $token->accessToken,
                    'expires_at' => $token->token->expires_at,
                ]
            ]
        ], 200);
    }

    /**
     * Sign in a user using username and password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signin(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // If validation fails, return validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validation failed!",
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // If authentication fails, return an error response
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials, Please check and try again!',
                'errors' => ['message' => 'Invalid credentials']
            ], 401);
        }

        // If authentication succeeds, get the authenticated user
        $user = Auth::user();

        // Issue token for the authenticated user
        $token = $user->createToken('App');

        // Return success response with token and user details
        return response()->json([
            'status' => true,
            'message' => 'User authenticated successfully.',
            'data' => [
                'user' => new UserResource($user),
                'token' => [
                    'type' => "Bearer",
                    'access_token' => $token->accessToken,
                    'expires_at' => $token->token->expires_at,
                ]
            ]
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // Validate the input
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|digits:10|unique:users,phone,' . $user->id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'business_name' => 'nullable|string|min:3|max:255',
            'business_type' => 'nullable|string|min:3|max:255',
            'business_legal_type' => 'nullable|string|min:3|max:255',
            'business_legal_number' => 'nullable|string|min:3|max:255',
            'fcm_token' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        // Directly update the user with the validated data
        $user->update($validatedData);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $customFileName = 'avatar' . '.' . $file->getClientOriginalExtension();

            $media = $user->addMediaFromRequest('image')
                ->usingFileName($customFileName)
                ->toMediaCollection('default');
            $user->avatar_url =$media->getUrl('thumb');
            $user->save();
        }

        // Return the updated user
        return response()->json([
            'status' => true,
            'message' => "Profile updated successfully!",
            'data' => new UserResource($user)
        ], 200);
    }

    public function generateApiKey(Request $request)
    {
        $user = auth()->user();

        $apiKey = $this->generateUniqueApiKey();
        $user->update([
            'api_key' => $apiKey
        ]);

        return response()->json([
            'status' => true,
            'message' => "Profile updated successfully!",
            'data' => new UserResource($user)
        ], 200);
    }

    protected function generateUniqueApiKey()
    {
        $apiKey = random_int(100000000, 999999999);

        while (User::where('api_key', $apiKey)->exists()) {
            $apiKey = random_int(100000000, 999999999);
        }

        return $apiKey;
    }
}
