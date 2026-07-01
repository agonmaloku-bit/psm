@extends('email.layout')

@section('header_title', 'Password Reset')

@section('content')
<p style="margin: 0 0 18px; color: #333; font-size: 15px; line-height: 1.6;">
    Hello {{ $user->first_name ?? '' }},
</p>

<p style="margin: 0 0 18px; color: #333; font-size: 15px; line-height: 1.6;">
    We received a request to reset your password. Click the button below to set a new password:
</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $resetUrl }}"
       style="display: inline-block; background-color: #1d4ed8; color: #ffffff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-size: 15px; font-weight: 600;">
        Reset Password
    </a>
</div>

<p style="margin: 0 0 18px; color: #666; font-size: 14px; line-height: 1.6;">
    This link will expire in <strong>60 minutes</strong>. If you did not request a password reset, you can safely ignore this email.
</p>

<hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">

<p style="margin: 0; color: #999; font-size: 12px; line-height: 1.5;">
    If the button doesn't work, copy and paste this link into your browser:<br>
    <a href="{{ $resetUrl }}" style="color: #1d4ed8; word-break: break-all;">{{ $resetUrl }}</a>
</p>
@endsection
