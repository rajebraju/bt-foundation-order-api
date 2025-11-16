<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>bcrypt($data['password']),
        ]);

        $user->assignRole('customer');

        $token = auth('api')->login($user);
        $refresh = $this->createRefreshToken($user->id);

        return response()->json([
            'access_token'=>$token,
            'refresh_token'=>$refresh,
            'expires_in'=>$this->getTTL(),
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message'=>'Unauthorized'], 401);
        }

        $user = auth('api')->user();
        $refresh = $this->createRefreshToken($user->id);

        return response()->json([
            'access_token'=>$token,
            'refresh_token'=>$refresh,
            'expires_in'=>$this->getTTL(),
        ]);
    }

    protected function createRefreshToken(int $userId)
    {
        $refresh = Str::random(80);

        DB::table('refresh_tokens')->insert([
            'user_id' => $userId,
            'token' => hash('sha256', $refresh),
            'expires_at' => Carbon::now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $refresh;
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token'=>'required|string'
        ]);

        $hashed = hash('sha256', $request->refresh_token);

        $row = DB::table('refresh_tokens')
            ->where('token', $hashed)
            ->first();

        if (!$row || now()->gt($row->expires_at)) {
            return response()->json(['message'=>'Invalid refresh token'], 401);
        }

        $user = User::find($row->user_id);

        if (!$user) {
            return response()->json(['message'=>'User not found'], 404);
        }

        // Rotate refresh token
        $newRefresh = Str::random(80);

        DB::table('refresh_tokens')
            ->where('id', $row->id)
            ->update([
                'token' => hash('sha256', $newRefresh),
                'expires_at' => now()->addDays(30),
                'updated_at' => now(),
            ]);

        $token = auth('api')->login($user);

        return response()->json([
            'access_token'=>$token,
            'refresh_token'=>$newRefresh,
            'expires_in'=>$this->getTTL(),
        ]);
    }

    public function logout(Request $request)
    {
        $refresh = $request->input('refresh_token');

        if ($refresh) {
            DB::table('refresh_tokens')
                ->where('token', hash('sha256',$refresh))
                ->delete();
        }

        auth('api')->logout();

        return response()->json(['message'=>'Logged out']);
    }

    /**
     * Calculate TTL without calling factory() or payload()
     * => NO IDE errors AND fully correct.
     */
    private function getTTL(): int
    {
        // Default in config/jwt.php: 'ttl' => 60 (minutes)
        $ttlMinutes = Config::get('jwt.ttl', 60);
        return $ttlMinutes * 60; // seconds
    }
}
