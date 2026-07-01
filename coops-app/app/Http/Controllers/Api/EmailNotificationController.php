<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailNotificationController extends Controller
{
    /**
     * Get all users (id, email, first_name, last_name) for recipient selection.
     */
    public function users(Request $request)
    {
        $this->authorizeSuperAdmin($request);

        $users = User::select('id', 'email', 'first_name', 'last_name')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('first_name')
            ->get();

        return ResponseResource::make(['data' => $users]);
    }

    /**
     * Send notification email to selected users.
     */
    public function send(Request $request)
    {
        $this->authorizeSuperAdmin($request);

        $data = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:50000',
        ]);

        $users = User::whereIn('id', $data['user_ids'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($users->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid recipients found.'], 422);
        }

        $subject = $data['subject'];
        $body = $data['body'];
        $sentCount = 0;

        foreach ($users as $user) {
            try {
                Mail::send('email.notification', ['body' => $body, 'user' => $user], function ($message) use ($user, $subject) {
                    $message->to($user->email);
                    $message->subject($subject);
                });
                $sentCount++;
            } catch (\Throwable $e) {
                // Continue sending to other recipients if one fails
                \Log::warning("Failed to send notification to {$user->email}: " . $e->getMessage());
            }
        }

        return ResponseResource::make([
            'data' => [
                'success' => true,
                'sent' => $sentCount,
                'total' => $users->count(),
                'message' => "Email sent to {$sentCount} of {$users->count()} recipients.",
            ],
        ]);
    }

    protected function authorizeSuperAdmin(Request $request): void
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('Super Admin')) {
            abort(403, 'Unauthorized.');
        }
    }
}
