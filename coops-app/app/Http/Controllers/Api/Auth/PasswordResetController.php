<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Return success even if user not found to prevent email enumeration
            return response()->json([
                'success' => true,
                'message' => 'If an account with that email exists, a reset link has been sent.',
            ]);
        }

        // Throttle: check if a token was created in the last 60 seconds
        $recent = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>', Carbon::now()->subSeconds(60))
            ->first();

        if ($recent) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another reset link.',
            ], 429);
        }

        // Delete old tokens for this email
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Create new token
        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Build reset URL pointing to the SPA
        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        $resetUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

        // Send email
        Mail::send('email.password-reset', ['resetUrl' => $resetUrl, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset - PSM');
        });

        return response()->json([
            'success' => true,
            'message' => 'If an account with that email exists, a reset link has been sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Check token expiry (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'This reset link has expired. Please request a new one.',
            ], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset successfully.',
        ]);
    }
}
