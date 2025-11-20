<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "User authentication endpoints")]
class AuthController extends Controller
{
    #[OA\Post(
        path: "/auth/register",
        tags: ["Auth"],
        summary: "Register a new user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["name", "email", "password"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "password", type: "string"),
                        new OA\Property(property: "role", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: "201", description: "User registered successfully")
        ]
    )]
    public function register(Request $request)
    {
        $request->validate([
            "name"     => "required|string|max:255",
            "email"    => "required|email|unique:users,email",
            "password" => "required|min:6",
            "role"     => "nullable|in:admin,vendor,customer"
        ]);

        $user = User::create([
            "name"     => $request->name,
            "email"    => $request->email,
            "password" => Hash::make($request->password),
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($request->role ?? 'customer');
        }

        return response()->json([
            "message" => "User registered successfully",
            "user"    => $user
        ], 201);
    }

    #[OA\Post(
        path: "/auth/login",
        tags: ["Auth"],
        summary: "Login user and return JWT token",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["email", "password"],
                    properties: [
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "password", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: "200", description: "Login successful"),
            new OA\Response(response: "401", description: "Invalid credentials")
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = Auth::user();

            // Try refresh token
            try {
                $refresh = JWTAuth::setToken($token)->refresh();
            } catch (\Throwable $e) {
                $refresh = $token;
            }

            return response()->json([
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => config('jwt.ttl') * 60,
                'refresh_token' => $refresh,
                'user'         => $user
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    #[OA\Post(
        path: "/auth/refresh",
        tags: ["Auth"],
        summary: "Refresh JWT token",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["refresh_token"],
                    properties: [
                        new OA\Property(property: "refresh_token", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: "200", description: "Token refreshed"),
            new OA\Response(response: "401", description: "Invalid refresh token")
        ]
    )]
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required'
        ]);

        try {
            $oldToken = $request->refresh_token;
            $newToken = JWTAuth::setToken($oldToken)->refresh();

            try {
                $newRefresh = JWTAuth::setToken($newToken)->refresh();
            } catch (\Throwable $e) {
                $newRefresh = $newToken;
            }

            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => config('jwt.ttl') * 60,
                'refresh_token' => $newRefresh
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }
    }

    #[OA\Post(
        path: "/auth/logout",
        tags: ["Auth"],
        summary: "Logout user",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: "200", description: "Successfully logged out"),
        ]
    )]
    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();   
            JWTAuth::invalidate($token);    
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }

        return response()->json(['message' => 'Successfully logged out'], 200);
    }


}
