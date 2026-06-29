@extends('email.layout')

@section('header_title', 'Contract Management')

@section('content')
<p style="font-size: 15px; font-weight: 700; color: #222; margin: 0 0 10px;">New contract has been requested by {{ $name }}</p>

<p style="font-size: 14px; color: #555; margin: 0 0 20px;">A new contract has been assigned and requires your attention.</p>

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
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Contract Type</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ optional($contract->contract_type)->name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Contractor</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $contract->name_of_contractor ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Deadline</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">{{ $contract->deadline_from }} — {{ $contract->deadline_to ?? 'Open' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888; border-bottom: 1px solid #eee;">Total Price</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee;">€{{ number_format($contract->total_price ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-size: 13px; color: #888;">Responsible Person</td>
        <td style="padding: 8px 0; font-size: 14px; color: #333;">{{ optional($contract->responsiblePerson)->first_name }} {{ optional($contract->responsiblePerson)->last_name }}</td>
    </tr>
</table>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
    <tr>
        <td style="border-radius: 5px; text-align: center; background-color: #1d4ed8;">
            <a href="{{ config('app.url') }}/platform/apps/contracts" target="_blank" style="border: solid 1px #1d4ed8; border-radius: 5px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 14px; font-weight: bold; margin: 0; padding: 10px 25px; text-decoration: none; background-color: #1d4ed8; color: #ffffff;">Click here for more information</a>
        </td>
    </tr>
</table>
@endsection
