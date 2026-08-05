<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head><body style="margin:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#172033">
<table role="presentation" width="100%" style="padding:28px 12px"><tr><td align="center"><table role="presentation" width="100%" style="max-width:620px;background:#fff;border-radius:16px;overflow:hidden">
<tr><td style="padding:24px 30px;background:#356df3;color:#fff"><div style="font-size:22px;font-weight:700">{{ config('app.name') }}</div><div>Сообщение по вашей записи</div></td></tr>
<tr><td style="padding:30px"><p style="font-size:17px">Здравствуйте, {{ $appointment->client_name }}!</p><div style="font-size:16px;line-height:1.65">{!! nl2br(e($messageBody)) !!}</div>
<div style="margin-top:26px;background:#f7f8fa;border-radius:12px;padding:18px"><strong>{{ $appointment->service->name }}</strong><br><span style="color:#667085">{{ $appointment->appointment_start->format('d.m.Y H:i') }} · {{ $appointment->user->name }}</span></div>
<p style="margin-top:26px;color:#667085">С уважением,<br>{{ $senderName }}</p></td></tr></table></td></tr></table></body></html>
