@extends('email.layout')

@section('header_title', 'Bills & Invoices')

@section('content')
<p style="font-size: 15px; font-weight: 700; color: #222; margin: 0 0 10px;">New bill request is created by {{ $name }}</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee; width: 160px;">Bill Number</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $bill->bill_no ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Type</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $bill->type ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Value</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $bill->value ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Supplier</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ optional($bill->supplierName)->name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Department</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ optional($bill->departmentName)->name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888;">Description</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333;">{{ \Illuminate\Support\Str::limit($bill->description ?? '-', 200) }}</td>
    </tr>
</table>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
    <tr>
        <td style="border-radius: 5px; text-align: center; background-color: #1d4ed8;">
            <a href="{{ config('app.url') }}/platform/apps/bills" target="_blank" style="border: solid 1px #1d4ed8; border-radius: 5px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 14px; font-weight: bold; margin: 0; padding: 10px 25px; text-decoration: none; background-color: #1d4ed8; color: #ffffff;">Click here for more information</a>
        </td>
    </tr>
</table>
@endsection
