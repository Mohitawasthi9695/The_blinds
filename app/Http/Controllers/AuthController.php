<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\DB;

class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        try {
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('The login credentials are incorrect.', 401);
            }

            if ($user->tokens()->count() > 0) {
                return $this->errorResponse('You are already logged in on another device.', 403);
            }

            $token = $user->createToken("{$user->role}_token")->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'user' => $user,
            ], "Login successful",200);
        } catch (\Throwable $e) {
            return $this->errorResponse("An unexpected error occurred. Please try again later.", 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse([], "Logout successful");
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
