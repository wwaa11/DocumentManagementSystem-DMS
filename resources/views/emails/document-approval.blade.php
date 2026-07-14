<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background-color:#0f766e;padding:28px 32px;">
                            <p style="margin:0 0 6px;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;color:#99f6e4;font-weight:600;">
                                {{ $appName }}
                            </p>
                            <h1 style="margin:0;font-size:22px;line-height:1.35;color:#ffffff;font-weight:700;">
                                มีเอกสารรอการอนุมัติ
                            </h1>
                            <p style="margin:10px 0 0;font-size:14px;line-height:1.5;color:#ccfbf1;">
                                กรุณาตรวจสอบและดำเนินการภายในระบบ
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px 8px;">
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#334155;">
                                สวัสดีครับ/ค่ะ<br>
                                มีเอกสารใหม่ส่งถึงคุณเพื่อขออนุมัติ กรุณาตรวจสอบรายละเอียดด้านล่าง
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                                <tr>
                                    <td style="padding:20px 22px;">
                                        <p style="margin:0 0 4px;font-size:12px;letter-spacing:0.04em;text-transform:uppercase;color:#64748b;font-weight:600;">
                                            หัวข้อเอกสาร
                                        </p>
                                        <p style="margin:0 0 18px;font-size:17px;line-height:1.4;color:#0f172a;font-weight:700;">
                                            {{ $title }}
                                        </p>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="50%" valign="top" style="padding:0 8px 14px 0;">
                                                    <p style="margin:0 0 4px;font-size:12px;color:#64748b;font-weight:600;">ประเภท</p>
                                                    <p style="margin:0;font-size:14px;color:#1e293b;">{{ $documentType }}</p>
                                                </td>
                                                <td width="50%" valign="top" style="padding:0 0 14px 8px;">
                                                    <p style="margin:0 0 4px;font-size:12px;color:#64748b;font-weight:600;">เลขที่อ้างอิง</p>
                                                    <p style="margin:0;font-size:14px;color:#1e293b;">#{{ $documentId }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%" valign="top" style="padding:0 8px 0 0;">
                                                    <p style="margin:0 0 4px;font-size:12px;color:#64748b;font-weight:600;">ผู้ขอ</p>
                                                    <p style="margin:0;font-size:14px;color:#1e293b;">{{ $requesterName }}</p>
                                                </td>
                                                <td width="50%" valign="top" style="padding:0 0 0 8px;">
                                                    <p style="margin:0 0 4px;font-size:12px;color:#64748b;font-weight:600;">วันที่ยื่น</p>
                                                    <p style="margin:0;font-size:14px;color:#1e293b;">{{ $submittedAt }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if ($detail !== '')
                                <p style="margin:22px 0 8px;font-size:12px;letter-spacing:0.04em;text-transform:uppercase;color:#64748b;font-weight:600;">
                                    รายละเอียด
                                </p>
                                <div style="margin:0 0 8px;padding:16px 18px;background-color:#ffffff;border:1px solid #e2e8f0;border-left:4px solid #0f766e;border-radius:8px;font-size:14px;line-height:1.65;color:#334155;">
                                    {!! $detail !!}
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:20px 32px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="border-radius:8px;background-color:#0f766e;">
                                        <a href="{{ $approveUrl }}" target="_blank" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;line-height:1.2;">
                                            เปิดเอกสารเพื่ออนุมัติ
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:16px 0 0;font-size:12px;line-height:1.5;color:#94a3b8;">
                                หากปุ่มกดไม่ได้ ให้คัดลอกลิงก์นี้ไปเปิดในเบราว์เซอร์:<br>
                                <a href="{{ $approveUrl }}" style="color:#0f766e;word-break:break-all;">{{ $approveUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px;background-color:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:12px;line-height:1.55;color:#94a3b8;text-align:center;">
                                อีเมลฉบับนี้ส่งอัตโนมัติจากระบบ {{ $appName }}<br>
                                กรุณาอย่าตอบกลับอีเมลนี้โดยตรง
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
