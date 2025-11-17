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
                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                        new OA\Property(property: "email", type: "string", example: "john@example.com"),
                        new OA\Property(property: "password", type: "string", example: "password123"),
                        new OA\Property(property: "role", type: "string", example: "customer", enum: ["admin", "vendor", "customer"])
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: "201", description: "User registered successfully"),
            new OA\Response(response: "422", description: "Validation error")
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

        $role = $request->role ?? 'customer';
        $user->assignRole($role);

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
                        new OA\Property(property: "email", type: "string", example: "admin@email.com"),
                        new OA\Property(property: "password", type: "string", example: "password")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Login successful",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: "access_token", type: "string"),
                            new OA\Property(property: "token_type", type: "string", example: "bearer"),
                            new OA\Property(property: "expires_in", type: "integer", example: 3600),
                            new OA\Property(property: "refresh_token", type: "string")
                        ]
                    )
                )
            ),
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
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = Auth::user();

            // 🔥 FIX: refresh token must be generated from $token
            $refreshToken = JWTAuth::refresh($token);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'refresh_token' => $refreshToken,
                'user' => $user
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
            new OA\Response(
                response: "200",
                description: "Token refreshed",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: "access_token", type: "string"),
                            new OA\Property(property: "token_type", type: "string", example: "bearer"),
                            new OA\Property(property: "expires_in", type: "integer", example: 3600)
                        ]
                    )
                )
            ),
            new OA\Response(response: "401", description: "Invalid refresh token")
        ]
    )]
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required'
        ]);

        try {
            // 🔥 FIX: must refresh using the body refresh_token
            $newToken = JWTAuth::refresh($request->refresh_token);

            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'refresh_token' => $newToken,
            ]);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }
    }

    #[OA\Post(
        path: "/auth/logout",
        tags: ["Auth"],
        summary: "Logout and invalidate token",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: "200", description: "Successfully logged out"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {}

        return response()->json(['message' => 'Successfully logged out']);
    }
}
