<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#172033">
<table role="presentation" width="100%" style="padding:28px 12px"><tr><td align="center">
  <table role="presentation" width="100%" style="max-width:680px;background:#fff;border-radius:16px;overflow:hidden">
    <tr><td style="padding:24px 30px;background:#172033;color:#fff"><div style="font-size:22px;font-weight:700">Technical support request</div><div style="margin-top:5px;color:#d7deea">{{ $companyName }}</div></td></tr>
    <tr><td style="padding:30px">
      <table role="presentation" width="100%" style="border-collapse:collapse;margin-bottom:24px">
        <tr><td style="padding:7px 0;color:#667085;width:150px">Category</td><td style="padding:7px 0;font-weight:700">{{ $categoryLabel }}</td></tr>
        <tr><td style="padding:7px 0;color:#667085">Subject</td><td style="padding:7px 0;font-weight:700">{{ $requestSubject }}</td></tr>
        <tr><td style="padding:7px 0;color:#667085">Employee</td><td style="padding:7px 0">{{ $employee->name }} ({{ $employee->email }})</td></tr>
        <tr><td style="padding:7px 0;color:#667085">Role</td><td style="padding:7px 0">{{ $employee->role?->displayName() ?: '—' }}</td></tr>
        @if($pageUrl)<tr><td style="padding:7px 0;color:#667085">Page</td><td style="padding:7px 0"><a href="{{ $pageUrl }}">{{ $pageUrl }}</a></td></tr>@endif
        <tr><td style="padding:7px 0;color:#667085">Sent</td><td style="padding:7px 0">{{ now()->format('d.m.Y H:i:s T') }}</td></tr>
      </table>
      <div style="font-size:16px;line-height:1.65;background:#f7f8fa;border-radius:12px;padding:20px">{!! nl2br(e($requestMessage)) !!}</div>
      <p style="margin:24px 0 0;color:#667085">Reply to this email to contact {{ $employee->name }} directly.</p>
    </td></tr>
  </table>
</td></tr></table>
</body>
</html>
