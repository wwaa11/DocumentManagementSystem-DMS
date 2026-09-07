<?php

namespace App\Services\IT;

use App\Http\Requests\IT\StoreHisLogRequest;
use App\Models\HisLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HisLogService
{
    public function createForm(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('admin.it.his-logs.create', [
            'hisLog' => null,
            'selectedReceiverUserid' => $user->userid,
            'selectedFixer' => $user->name,
            'modules' => HisLog::moduleOptions(),
            'assignees' => $this->assigneeOptionsByDepartment(),
            'statuses' => HisLog::statusOptions(),
        ]);
    }

    public function store(StoreHisLogRequest $request): RedirectResponse
    {
        $time = $this->normalizeTime($request->string('time')->toString());
        $receiver = $this->resolveReceiver($request->string('receiver_userid')->toString());

        HisLog::query()->create([
            'reported_at' => $request->date('reported_at'),
            'reporter' => $request->string('reporter')->toString(),
            'module' => $request->string('module')->toString(),
            'problem_detail' => $request->input('problem_detail'),
            'receiver' => $receiver->name,
            'receiver_userid' => $receiver->userid,
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

    public function editForm(HisLog $hisLog): View
    {
        $selectedReceiverUserid = $hisLog->receiver_userid;

        if (! filled($selectedReceiverUserid) && filled($hisLog->receiver)) {
            $selectedReceiverUserid = HisLog::fixerUsers()
                ->firstWhere('name', $hisLog->receiver)
                ?->userid;
        }

        return view('admin.it.his-logs.create', [
            'hisLog' => $hisLog,
            'selectedReceiverUserid' => $selectedReceiverUserid,
            'selectedFixer' => $hisLog->fixer,
            'modules' => HisLog::moduleOptions(),
            'assignees' => $this->assigneeOptionsByDepartment(),
            'statuses' => HisLog::statusOptions(),
        ]);
    }

    public function update(StoreHisLogRequest $request, HisLog $hisLog): RedirectResponse
    {
        $time = $this->normalizeTime($request->string('time')->toString());
        $receiver = $this->resolveReceiver(
            $request->string('receiver_userid')->toString(),
            $hisLog
        );

        $hisLog->update([
            'reported_at' => $request->date('reported_at'),
            'reporter' => $request->string('reporter')->toString(),
            'module' => $request->string('module')->toString(),
            'problem_detail' => $request->input('problem_detail'),
            'receiver' => $receiver->name,
            'receiver_userid' => $receiver->userid,
            'fixer' => $request->input('fixer'),
            'root_cause' => $request->input('root_cause'),
            'status' => $request->string('status')->toString(),
            'time' => $time,
            'shift' => HisLog::resolveShiftFromTime($time),
        ]);

        return redirect()
            ->route('admin.it.hislogs.index')
            ->with('success', 'อัปเดต HIS Log สำเร็จ!');
    }

    public function index(Request $request): View
    {
        $filters = $this->filterInputs($request);
        $logs = $this->buildFilteredQuery($request)->paginate(20)->withQueryString();

        return view('admin.it.his-logs.index', [
            'logs' => $logs,
            ...$filters,
            'modules' => HisLog::moduleOptions(),
            'shifts' => HisLog::shiftOptions(),
            'statuses' => HisLog::statusOptions(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filterInputs($request);
        $includeDashboard = $request->boolean('dashboard');
        $query = $this->buildFilteredQuery($request);
        $logs = $query->get();

        $logRows = [$this->exportHeaders()];

        foreach ($logs as $index => $log) {
            $logRows[] = [
                $index + 1,
                $log->reported_at?->format('Y-m-d') ?? '',
                $log->reporter,
                $log->module,
                $log->problem_detail ?? '',
                $log->receiver,
                $log->fixer ?? '',
                $log->root_cause ?? '',
                $log->status,
                $log->shift,
                $log->time ?? '',
            ];
        }

        $exporter = new HisLogExcelExporter;

        if ($includeDashboard) {
            $stats = $this->buildDashboardStats($this->buildFilteredQuery($request));
            $sheets = [
                'Dashboard' => $this->buildDashboardExportRows($stats, $filters),
                'HIS_Log' => $logRows,
            ];
            $filename = 'his-log-dashboard-'.now()->format('Y-m-d_His').'.xlsx';
        } else {
            $sheets = [
                'HIS_Log' => $logRows,
            ];
            $filename = 'his-logs-'.now()->format('Y-m-d_His').'.xlsx';
        }

        return response()->streamDownload(
            static function () use ($exporter, $sheets): void {
                echo $exporter->buildSheets($sheets);
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function dashboard(Request $request): View
    {
        $filters = $this->filterInputs($request);
        $stats = $this->buildDashboardStats($this->buildFilteredQuery($request));

        return view('admin.it.his-logs.dashboard', [
            'stats' => $stats,
            ...$filters,
        ]);
    }

    /**
     * @return array{
     *     start_date: mixed,
     *     end_date: mixed,
     *     module: mixed,
     *     shift: mixed,
     *     status: mixed,
     *     problem_detail: mixed
     * }
     */
    private function filterInputs(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'module' => $request->input('module'),
            'shift' => $request->input('shift'),
            'status' => $request->input('status'),
            'problem_detail' => $request->input('problem_detail'),
        ];
    }

    /**
     * @return Builder<HisLog>
     */
    private function buildFilteredQuery(Request $request): Builder
    {
        $filters = $this->filterInputs($request);

        $query = HisLog::query()->orderByDesc('reported_at')->orderByDesc('time');

        if (filled($filters['start_date'])) {
            $query->whereDate('reported_at', '>=', $filters['start_date']);
        }

        if (filled($filters['end_date'])) {
            $query->whereDate('reported_at', '<=', $filters['end_date']);
        }

        if (filled($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (filled($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        if (filled($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['problem_detail'])) {
            $query->where('problem_detail', 'like', '%'.$filters['problem_detail'].'%');
        }

        return $query;
    }

    /**
     * @param  array{
     *     total: int,
     *     closed: int,
     *     close_rate: float,
     *     status_counts: array<string, int>,
     *     top_modules: array<string, int>,
     *     shift_counts: array<string, int>
     * }  $stats
     * @param  array{
     *     start_date: mixed,
     *     end_date: mixed,
     *     module: mixed,
     *     shift: mixed,
     *     status: mixed,
     *     problem_detail: mixed
     * }  $filters
     * @return list<list<string|int|float|null>>
     */
    private function buildDashboardExportRows(array $stats, array $filters): array
    {
        $periodLabel = collect([$filters['start_date'], $filters['end_date']])
            ->filter()
            ->implode(' → ') ?: 'ทั้งหมด';
        $activeCases = ($stats['status_counts']['Open'] ?? 0) + ($stats['status_counts']['In Progress'] ?? 0);

        $rows = [
            ['HIS Log Dashboard Summary'],
            ['ช่วงเวลา', $periodLabel],
            [],
            ['Metric', 'Value'],
            ['Total Cases', $stats['total']],
            ['Closed', $stats['closed']],
            ['Close Rate (%)', $stats['close_rate']],
            ['Active Cases', $activeCases],
            [],
            ['Status', 'Count'],
        ];

        foreach ($stats['status_counts'] as $status => $count) {
            $rows[] = [$status, $count];
        }

        $rows[] = [];
        $rows[] = ['Shift', 'Count'];

        foreach ($stats['shift_counts'] as $shift => $count) {
            $rows[] = [$shift, $count];
        }

        $rows[] = [];
        $rows[] = ['Top Modules', 'Count'];

        foreach ($stats['top_modules'] as $module => $count) {
            $rows[] = [$module, $count];
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function exportHeaders(): array
    {
        return [
            'No.',
            'วันที่แจ้ง',
            'ผู้แจ้ง/แผนก',
            'module',
            'รายละเอียดปัญหา',
            'ผู้รับเรื่อง',
            'ผู้แก้ไข',
            'วิธีแก้ไข/root cause',
            'สถานะ',
            'shif',
            'time',
        ];
    }

    /**
     * @param  Builder<HisLog>  $query
     * @return array{
     *     total: int,
     *     closed: int,
     *     close_rate: float,
     *     status_counts: array<string, int>,
     *     top_modules: array<string, int>,
     *     shift_counts: array<string, int>
     * }
     */
    public function buildDashboardStats(Builder $query): array
    {
        $records = $query->get(['module', 'status', 'shift']);
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
            'shift_counts' => $shiftCounts,
        ];
    }

    private function normalizeTime(string $time): string
    {
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches) === 1) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        throw new RuntimeException('รูปแบบเวลาไม่ถูกต้อง');
    }

    /**
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, User>>
     */
    private function assigneeOptionsByDepartment()
    {
        return HisLog::fixerUsers()->groupBy(
            fn (User $user): string => filled($user->department) ? $user->department : 'ไม่ระบุแผนก'
        );
    }

    private function resolveReceiver(string $receiverUserid, ?HisLog $hisLog = null): User
    {
        $receiver = User::query()
            ->where('userid', $receiverUserid)
            ->whereIn('role', HisLog::fixerRoles())
            ->first(['userid', 'name', 'role']);

        if ($receiver instanceof User) {
            return $receiver;
        }

        if (
            $hisLog instanceof HisLog
            && filled($hisLog->receiver_userid)
            && (string) $hisLog->receiver_userid === $receiverUserid
            && filled($hisLog->receiver)
        ) {
            return new User([
                'userid' => $hisLog->receiver_userid,
                'name' => $hisLog->receiver,
            ]);
        }

        throw new RuntimeException('ผู้รับเรื่องที่เลือกไม่ถูกต้อง');
    }
}
