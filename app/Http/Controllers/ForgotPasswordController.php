<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ForgotPasswordController extends ApiController
{
    // Send Reset Code
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;

        // Rate Limiting
        if (RateLimiter::tooManyAttempts('forgot-password:' . $email, 3)) {
            return $this->errorResponse('Too many attempts. Try again later.', 429);
        }

        RateLimiter::hit('forgot-password:' . $email, 60);

        // Generate a reset token
        $token = rand(100000, 999999);

        // Store the token in the password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send the reset code via email
        Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($email) {
            $message->to($email)->subject('Password Reset Code');
        });

        return $this->successResponse(null, 'Reset code sent successfully.');
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'reset_code' => 'required|digits:6',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        $email = $request->email;
        $resetCode = $request->reset_code;

        $resetTokenEntry = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$resetTokenEntry) {
            return $this->errorResponse('Invalid or expired reset code.', 400);
        }

        if (!Hash::check($resetCode, $resetTokenEntry->token)) {
            return $this->errorResponse('Invalid reset code.', 400);
        }

        if (Carbon::parse($resetTokenEntry->created_at)->addMinutes(15)->isPast()) {
            return $this->errorResponse('Reset code has expired.', 400);
        }

        $user = User::where('email', $email)->first();
        $user->password = Hash::make($request->newPassword);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return $this->successResponse(null, 'Password has been reset successfully.');
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match our records.'],
            ]);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->successResponse(null, 'Password has been changed successfully.');
    }

}
