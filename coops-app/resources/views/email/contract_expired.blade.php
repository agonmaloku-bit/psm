@extends('email.layout')

@section('header_title', 'Contract Management')

@section('content')
<p style="font-size: 15px; font-weight: 700; color: #c0392b; margin: 0 0 10px;">❌ Contract Has Expired</p>

<p style="font-size: 14px; color: #555; margin: 0 0 20px;">The following contract has reached its expiration date. Please review and take the necessary action.</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee; width: 160px;">Contract Name</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $contract->name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Serial Number</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $contract->serial_number ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Contractor</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $contract->name_of_contractor ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Total Price</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">€{{ number_format($contract->total_price ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888;">Expired On</td>
        <td style="padding: 8px 0; font-size: 14px; font-weight: 700; color: #c0392b;">{{ $expiredDate }}</td>
    </tr>
</table>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
    <tr>
        <td style="border-radius: 5px; text-align: center; background-color: #c0392b;">
            <a href="{{ config('app.url') }}/platform/apps/contracts" target="_blank" style="border: solid 1px #c0392b; border-radius: 5px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 14px; font-weight: bold; margin: 0; padding: 10px 25px; text-decoration: none; background-color: #c0392b; color: #ffffff;">View Contract</a>
        </td>
    </tr>
</table>
@endsection
