<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ config('app.name') }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            table.body .container { width: 100% !important; padding: 10px !important; }
            table.body .main { border-radius: 0 !important; }
        }
    </style>
</head>
<body style="background-color: #e8e8e8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; background-color: #e8e8e8; width: 100%;" width="100%">
        <tr>
            <td>&nbsp;</td>
            <td class="container" style="display: block; max-width: 580px; padding: 30px 10px; width: 580px; margin: 0 auto;">

                {{-- HEADER --}}
                <div style="text-align: center; padding: 20px 0 15px;">
                    <span style="font-size: 18px; font-weight: 600; color: #333;">{{ config('app.name') }} - @yield('header_title', 'Notification')</span>
                </div>

                {{-- MAIN CARD --}}
                <table role="presentation" class="main" style="border-collapse: separate; background: #ffffff; border-radius: 6px; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" width="100%">
                    <tr>
                        <td style="padding: 30px 35px;">
                            @yield('content')
                        </td>
                    </tr>
                </table>

                {{-- FOOTER --}}
                <div style="text-align: center; padding: 15px 0; color: #999; font-size: 12px;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>

            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>
