<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailSubject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f2f3f7;font-family:'Segoe UI',Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f3f7;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#071b2a,#0b2940);padding:28px 32px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:0.3px;">{{ $headline }}</h1>
                        </td>
                    </tr>
                    @if($intro)
                    <tr>
                        <td style="padding:24px 32px 8px 32px;">
                            <p style="margin:0;color:#3a4150;font-size:15px;line-height:1.6;">{{ $intro }}</p>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding:16px 32px 8px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach($lines as $line)
                                <tr>
                                    <td style="padding:8px 0;border-bottom:1px solid #eef0f4;vertical-align:top;">
                                        <span style="color:#8a92a6;font-size:13px;">{{ $line['label'] }}</span>
                                    </td>
                                    <td style="padding:8px 0 8px 16px;border-bottom:1px solid #eef0f4;text-align:right;color:#1c2838;font-size:14px;font-weight:600;">
                                        {!! nl2br(e($line['value'])) !!}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                    @if($footer)
                    <tr>
                        <td style="padding:20px 32px 28px 32px;">
                            <p style="margin:0;color:#a3a9bb;font-size:12px;line-height:1.6;">{{ $footer }}</p>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
