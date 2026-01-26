<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path("fonts/THSarabunNew.ttf") }}") format('truetype');
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path("fonts/THSarabunNew-Bold.ttf") }}") format('truetype');
        }

        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 14pt;
            line-height: 1.1;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 14pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 3px 5px;
            text-align: left;
            font-size: 12pt;
        }

        th {
            background-color: #f5f5f5;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 25px;
            font-size: 10pt;
            text-align: right;
            opacity: 0.6;
        }

        .meta-table td {
            border: none;
            padding: 2px 0;
            font-size: 13pt;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin:0; font-size: 18pt;">ใบบันทึกการฝึกอบรมภาคอิสระ</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;"><strong>ผู้ขอเอกสาร:</strong> {{ $project->creator->name }}</td>
            <td style="width: 50%; text-align: right;"><strong>วันที่สร้าง:</strong> {{ $project->created_at->format("d/m/Y") }}</td>
        </tr>
        <tr>
            <td><strong>แผนก:</strong> {{ $project->creator->department }}</td>
            <td style="text-align: right;"><strong>เลขที่เอกสาร:</strong> #{{ $project->id }}</td>
        </tr>
    </table>

    <div class="section-title">รายละเอียดหลักสูตร </div>
    <div style="margin-bottom: 10px;">
        <div><strong>ชื่อหลักสูตร:</strong> {{ $project->title }}</div>
        <div><strong>ที่มาของหลักสูตร:</strong> {{ $project->detail }}</div>
        <div><strong>กำหนดการ:</strong> {{ $project->start_date->format("d/m/Y") }} ถึง {{ $project->end_date->format("d/m/Y") }}</div>
        <div><strong>เวลา:</strong> {{ $project->start_time->format("H:i") }} - {{ $project->end_time->format("H:i") }} (รวม {{ $project->hours }} ชม. {{ $project->minutes }} น.)</div>
    </div>

    <div class="section-title">รายชื่อวิทยากร</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">ลำดับ</th>
                <th style="width: 100px;">รหัสพนักงาน</th>
                <th>ชื่อ-นามสกุล</th>
                <th>ตำแหน่ง</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->mentors as $index => $mentor)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $mentor->mentor }}</td>
                    <td>{{ $mentor->mentor_name }}</td>
                    <td>{{ $mentor->mentor_position }}</td>
                </tr>
            @endforeach
            @if ($project->mentors->count() == 0)
                <tr>
                    <td class="text-center" colspan="4">ไม่พบข้อมูลวิทยากร</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">รายชื่อผู้เข้าร่วมและการประเมินผล</div>
    <table>
        <thead>
            <tr>
                <th style="width: 35px;">ที่</th>
                <th style="width: 80px;">รหัส</th>
                <th style="width: 160px;">ชื่อ-นามสกุล</th>
                <th>ตำแหน่ง</th>
                <th>แผนก</th>
                <th style="width: 40px;">วิธี</th>
                <th style="width: 40px;">ผล</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->participants as $index => $participant)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-mono">{{ $participant->participant }}</td>
                    <td>{{ $participant->participant_name }}</td>
                    <td>{{ $participant->participant_position }}</td>
                    <td>{{ $participant->participant_department }}</td>
                    <td class="text-center">{{ $participant->assetment_type ?? "-" }}</td>
                    <td class="text-center"><strong>{{ $participant->score ?? "-" }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 10pt; line-height: 1.2;">
        <strong>หมายเหตุ :</strong><br>
        • <strong>วิธีการประเมิน :</strong> P = ฝึกปฏิบัติจริง, O = สังเกตการปฏิบัติงาน, I = ถาม-ตอบ<br>
        • <strong>ระดับการประเมิณผล :</strong> 3 = ปฏิบัติงานและแก้ไขปัญหาได้, 2 = ปฏิบัติงานและแก้ไขปัญหาได้บ้าง, 1 = การปฏิบัติงานยังต้องปรับปรุง
    </div>

    <div style="margin-top: 15px; font-size: 14pt; line-height: 1.2;">
        <strong>ผู้รับผิดชอบโครงการ:</strong> {{ $project_owner->name }} ({{ $project_owner->department }})<br>
        <strong>วันที่:</strong> {{ $project_date->format("d/m/Y H:i") }}
    </div>

    <div class="footer">
        พิมพ์จากระบบ DMS (Document Management System) เมื่อวันที่ {{ date("d/m/Y H:i") }}
    </div>
</body>

</html>
