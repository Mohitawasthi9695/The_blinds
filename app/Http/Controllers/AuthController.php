<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::with('roles:name')->where('email', $validated['email'])->first();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        Log::info($user);
        try {
            if (!$user || !Hash::check($validated['password'], $user->password) || $user->status == 0) {
                return $this->errorResponse('The login credentials are incorrect.', 401);
            }
            // if ($user->tokens()->count() > 0) {
            //     return $this->errorResponse('You are already logged in on another device.', 403);
            // }
            $token = $user->createToken("{$user->name}_token")->plainTextToken;
            $user = [
                'id'                => $user->id,
                'name'              => $user->name,
                'username'          => $user->username,
                'email'             => $user->email,
                'phone'             => $user->phone,
                'status'            => $user->status,
                'ip'                => $user->ip,
                'email_verified_at' => $user->email_verified_at,
                'created_at'        => $user->created_at,
                'updated_at'        => $user->updated_at,
                'roles'             => $user->roles->pluck('name')->implode(', '),
            ];
            return response()->json([
                'message' => "Login successful",
                'user' => $user,
                'access_token' => $token,
            ], 200);
        } catch (\Throwable $e) {
            return $this->errorResponse("An unexpected error occurred. Please try again later.", 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => "Logout successful"], 200);
        } catch (\Throwable $e) {
            return $this->errorResponse("An error occurred during logout. Please try again later.", 500);
        }
    }
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'old_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            $user = User::where('email', $request->email)->firstOrFail();

            if (!Hash::check($request->old_password, $user->password)) {
                return $this->errorResponse('The old password is incorrect.', 401); // Unauthorized
            }

            if (Hash::check($request->new_password, $user->password)) {
                return $this->errorResponse('The new password must be different from the old password.', 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->successResponse('', 'Password has been reset successfully.', 200); // OK

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('The provided email does not exist.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while resetting the password.', 500, $e->getMessage());
        }
    }
}
