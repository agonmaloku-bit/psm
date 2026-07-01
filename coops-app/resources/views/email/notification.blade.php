@extends('email.layout')

@section('header_title', 'Notification')

@section('content')
<p style="margin: 0 0 18px; color: #333; font-size: 15px; line-height: 1.6;">
    Hello {{ $user->first_name ?? '' }},
</p>

<div style="margin: 0 0 18px; color: #333; font-size: 15px; line-height: 1.7;">
    {!! nl2br(e($body)) !!}
</div>
@endsection
