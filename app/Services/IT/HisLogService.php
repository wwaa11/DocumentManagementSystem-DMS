<?php

namespace App\Services\IT;

use App\Http\Requests\IT\ImportHisLogRequest;
use App\Http\Requests\IT\StoreHisLogRequest;
use App\Models\HisLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;
use ZipArchive;

class HisLogService
{
    public function createForm(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('admin.it.his-logs.create', [
            'receiver' => $user->name,
            'modules' => HisLog::moduleOptions(),
            'issues' => HisLog::issueOptions(),
            'statuses' => HisLog::statusOptions(),
        ]);
    }

    public function store(StoreHisLogRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $time = $this->normalizeTime($request->string('time')->toString());

        HisLog::query()->create([
            'reported_at' => $request->date('reported_at'),
            'reporter' => $request->string('reporter')->toString(),
            'module' => $request->string('module')->toString(),
            'issues' => $request->input('issues', []),
            'problem_detail' => $request->input('problem_detail'),
            'receiver' => $user->name,
            'receiver_userid' => $user->userid,
            'fixer' => $request->input('fixer'),
            'root_cause' => $request->input('root_cause'),
            'status' => $request->string('status')->toString(),
            'time' => $time,
            'shift' => HisLog::resolveShiftFromTime($time),
        ]);

        return redirect()
            ->route('admin.it.hislogs.create')
            ->with('success', 'บันทึก HIS Log สำเร็จ!');
    }

    public function dashboard(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = HisLog::query()->orderByDesc('reported_at')->orderByDesc('time');

        if ($startDate) {
            $query->whereDate('reported_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('reported_at', '<=', $endDate);
        }

        $logs = (clone $query)->paginate(20)->withQueryString();
        $stats = $this->buildDashboardStats(clone $query);

        return view('admin.it.his-logs.dashboard', [
            'logs' => $logs,
            'stats' => $stats,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'modules' => HisLog::moduleOptions(),
            'statuses' => HisLog::statusOptions(),
        ]);
    }

    public function import(ImportHisLogRequest $request): RedirectResponse
    {
        $path = $request->file('excel_file')?->getRealPath();

        if ($path === false || $path === null) {
            return redirect()
                ->route('admin.it.hislogs.dashboard')
                ->with('error', 'ไม่สามารถอ่านไฟล์ Excel ได้');
        }

        try {
            $rows = $this->readHisLogSheet($path);
            $imported = 0;

            foreach ($rows as $row) {
                $payload = $this->mapImportRow($row);

                if ($payload === null) {
                    continue;
                }

                HisLog::query()->create($payload);
                $imported++;
            }
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.it.hislogs.dashboard')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.it.hislogs.dashboard')
            ->with('success', "นำเข้าข้อมูลสำเร็จ {$imported} รายการ");
    }

    /**
     * @return array{
     *     total: int,
     *     closed: int,
     *     close_rate: float,
     *     status_counts: array<string, int>,
     *     top_modules: array<string, int>,
     *     top_issues: array<string, int>,
     *     shift_counts: array<string, int>
     * }
     */
    public function buildDashboardStats($query): array
    {
        $records = $query->get(['module', 'issues', 'status', 'shift']);
        $total = $records->count();
        $closed = $records->where('status', 'Closed')->count();

        $statusCounts = [];
        foreach (HisLog::statusOptions() as $status) {
            $statusCounts[$status] = $records->where('status', $status)->count();
        }

        $moduleCounts = $records
            ->groupBy('module')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(10)
            ->all();

        $issueCounts = [];
        foreach ($records as $record) {
            foreach (($record->issues ?? []) as $issue) {
                $issueCounts[$issue] = ($issueCounts[$issue] ?? 0) + 1;
            }
        }
        arsort($issueCounts);
        $issueCounts = array_slice($issueCounts, 0, 5, true);

        $shiftCounts = [];
        foreach (HisLog::shiftOptions() as $shift) {
            $shiftCounts[$shift] = $records->where('shift', $shift)->count();
        }

        return [
            'total' => $total,
            'closed' => $closed,
            'close_rate' => $total > 0 ? round(($closed / $total) * 100, 1) : 0.0,
            'status_counts' => $statusCounts,
            'top_modules' => $moduleCounts,
            'top_issues' => $issueCounts,
            'shift_counts' => $shiftCounts,
        ];
    }

    /**
     * @return list<array<int, string|null>>
     */
    private function readHisLogSheet(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('ไฟล์ Excel ไม่ถูกต้อง');
        }

        $sharedStrings = $this->parseSharedStrings($zip->getFromName('xl/sharedStrings.xml') ?: '');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('ไม่พบชีต HIS_Log ในไฟล์');
        }

        $rows = $this->parseSheetRows($sheetXml, $sharedStrings);

        if ($rows === []) {
            throw new RuntimeException('ไม่พบข้อมูลในไฟล์ Excel');
        }

        $header = array_map(fn ($value) => $this->normalizeHeader((string) $value), $rows[0]);
        $expected = ['no.', 'วันที่แจ้ง', 'ผู้แจ้ง/แผนก', 'module', 'รายละเอียดปัญหา', 'ผู้รับเรื่อง', 'ผู้แก้ไข', 'วิธีแก้ไข/root cause', 'สถานะ', 'shif', 'time'];

        foreach ($expected as $index => $label) {
            if (($header[$index] ?? null) !== $label) {
                throw new RuntimeException('รูปแบบไฟล์ไม่ตรงกับ HIS_Log_Dashboard.xlsx (ชีต HIS_Log)');
            }
        }

        return array_slice($rows, 1);
    }

    /**
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>|null
     */
    private function mapImportRow(array $row): ?array
    {
        $reportedAt = $this->parseExcelDate($row[1] ?? null);
        $reporter = trim((string) ($row[2] ?? ''));
        $module = trim((string) ($row[3] ?? ''));
        $problemDetail = trim((string) ($row[4] ?? ''));
        $receiver = trim((string) ($row[5] ?? ''));
        $fixer = trim((string) ($row[6] ?? ''));
        $rootCause = trim((string) ($row[7] ?? ''));
        $status = trim((string) ($row[8] ?? ''));
        $time = $this->parseExcelTime($row[10] ?? null);

        if ($reportedAt === null || $reporter === '' || $module === '' || $receiver === '' || $time === null) {
            return null;
        }

        if (! in_array($status, HisLog::statusOptions(), true)) {
            $status = 'Open';
        }

        return [
            'reported_at' => $reportedAt,
            'reporter' => $reporter,
            'module' => $module,
            'issues' => [],
            'problem_detail' => $problemDetail !== '' ? $problemDetail : null,
            'receiver' => $receiver,
            'receiver_userid' => null,
            'fixer' => $fixer !== '' ? $fixer : null,
            'root_cause' => $rootCause !== '' ? $rootCause : null,
            'status' => $status,
            'time' => $time,
            'shift' => HisLog::resolveShiftFromTime($time),
        ];
    }

    /**
     * @return list<string>
     */
    private function parseSharedStrings(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $document = simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $document->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $strings = [];

        foreach ($document->xpath('//m:si') ?: [] as $si) {
            $value = '';
            foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $text) {
                $value .= (string) $text;
            }
            $strings[] = $value;
        }

        return $strings;
    }

    /**
     * @param  list<string>  $sharedStrings
     * @return list<array<int, string|null>>
     */
    private function parseSheetRows(string $sheetXml, array $sharedStrings): array
    {
        $document = simplexml_load_string($sheetXml);

        if ($document === false) {
            throw new RuntimeException('ไม่สามารถอ่านชีต Excel ได้');
        }

        $document->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($document->xpath('//m:sheetData/m:row') ?: [] as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $colIndex = $this->columnIndexFromReference($ref);
                $type = (string) $cell['t'];
                $raw = isset($cell->v) ? (string) $cell->v : null;

                if ($raw === null) {
                    $cells[$colIndex] = null;

                    continue;
                }

                $cells[$colIndex] = $type === 's'
                    ? ($sharedStrings[(int) $raw] ?? null)
                    : $raw;
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);
            $max = max(array_keys($cells));
            $ordered = [];

            for ($i = 0; $i <= $max; $i++) {
                $ordered[$i] = $cells[$i] ?? null;
            }

            $rows[] = $ordered;
        }

        return $rows;
    }

    private function columnIndexFromReference(string $reference): int
    {
        preg_match('/^([A-Z]+)/', $reference, $matches);
        $letters = $matches[1] ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function normalizeHeader(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
    }

    private function parseExcelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromDate(1899, 12, 30)
                ->addDays((int) $value)
                ->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseExcelTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $numeric = (float) $value;

            // Excel time serial (fraction of day)
            if ($numeric > 0 && $numeric < 1) {
                $totalMinutes = (int) round($numeric * 24 * 60);
                $hour = intdiv($totalMinutes, 60) % 24;
                $minute = $totalMinutes % 60;

                return sprintf('%02d:%02d', $hour, $minute);
            }

            // HIS template stores time as H.MM (e.g. 8.02 => 08:02)
            $hour = (int) floor($numeric);
            $minute = (int) round(($numeric - $hour) * 100);

            if ($minute >= 60) {
                $hour += intdiv($minute, 60);
                $minute = $minute % 60;
            }

            $hour = $hour % 24;

            return sprintf('%02d:%02d', $hour, $minute);
        }

        $string = trim((string) $value);

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $string, $matches) === 1) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        if (preg_match('/^(\d{1,2})\.(\d{1,2})$/', $string, $matches) === 1) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return null;
    }

    private function normalizeTime(string $time): string
    {
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches) === 1) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        throw new RuntimeException('รูปแบบเวลาไม่ถูกต้อง');
    }
}
