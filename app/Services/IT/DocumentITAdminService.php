<?php

namespace App\Services\IT;

use App\Models\DocumentBorrow;
use App\Models\DocumentIT;
use App\Models\DocumentItUser;
use App\Models\Hardware;
use App\Models\Log;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentITAdminService
{
    public function __construct(private DocumentWorkflowService $workflow) {}

    private function chatAwareItQuery(): Builder
    {
        return DocumentIT::query()->withCount('messages');
    }

    private function chatAwareItUserQuery(): Builder
    {
        return DocumentItUser::query()->withCount('messages');
    }

    public function adminDocumentCount(): JsonResponse
    {
        $documentListAll = DocumentIT::whereIn('status', ['pending', 'process', 'done'])->get();
        $documentListNewHardware = $documentListAll->where('status', 'pending')->filter(function ($item) {
            return blank($item->assigned_user_id)
                && $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
        })->count();
        $documentListNew = $documentListAll->where('status', 'pending')->filter(function ($item) {
            return blank($item->assigned_user_id)
                && ! $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
        })->count();
        $documentListApprove = $documentListAll->where('status', 'done')->count();
        $documentListMy = $documentListAll->filter(function ($item) {
            return $item->assigned_user_id === auth()->user()->userid
                && in_array($item->status, ['process', 'pending'], true);
        })->count();

        $documentITUserList = DocumentItUser::whereIn('status', ['pending', 'process', 'done'])->get();
        $documentITUserListNew = $documentITUserList->where('status', 'pending')->filter(function ($item) {
            return blank($item->assigned_user_id)
                && ! $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();
        })->count();
        $documentITUserListApprove = $documentITUserList->where('status', 'done')->count();
        $documentITUserListMy = $documentITUserList->filter(function ($item) {
            return $item->assigned_user_id === auth()->user()->userid
                && in_array($item->status, ['process', 'pending'], true);
        })->count();

        $documentBorrowList = DocumentBorrow::whereIn('status', ['pending', 'borrow_approve', 'return_approve', 'return'])->get();
        $documentListBorrowHardware = $documentBorrowList->where('status', 'pending')->filter(function ($item) {
            return $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->where('status', 'wait')->first();
        })->count();
        $documentListBorrowNew = $documentBorrowList->whereIn('status', ['pending', 'return_approve'])->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();

            return ! $task || $task->status != 'wait';
        })->count();
        $documentListBorrowApprove = $documentBorrowList->whereIn('status', ['borrow_approve', 'return'])->count();

        return response()->json([
            'it.hardware' => $documentListNewHardware + $documentListBorrowHardware,
            'it.approve' => $documentListApprove + $documentITUserListApprove + $documentListBorrowApprove,
            'it.borrow' => $documentListBorrowNew,
            'it.new' => $documentListNew + $documentITUserListNew,
            'it.my' => $documentListMy + $documentITUserListMy,
        ]);
    }

    public function adminHardwareDocuments(): View
    {
        $documentListAll = $this->chatAwareItQuery()->where('status', 'pending')->get();
        $documents = $documentListAll->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();

            return $task;
        });
        $documentBorrowList = DocumentBorrow::where('status', 'pending')->get();
        $documentsborrow = $documentBorrowList->filter(function ($item) {
            $task = $item->tasks()->where('step', 2)->where('task_user', 'IT Unit Support')->first();

            return $task;
        });
        $documents = $this->mergeDocumentCollections($documents, $documentsborrow)->sortBy('created_at');
        $action = 'hardware';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminApproveDocuments(): View
    {
        $with = ['approvers.user', 'creator', 'tasks.user', 'logs.user'];
        $documents = $this->chatAwareItQuery()->where('status', 'done')->with($with)->get();
        $documentsITUser = $this->chatAwareItUserQuery()
            ->where('status', 'done')
            ->with([...$with, 'documentUser'])
            ->get();
        $documentsBorrow = DocumentBorrow::query()
            ->whereIn('status', ['borrow_approve', 'return'])
            ->with($with)
            ->get();
        $documents = $this->mergeDocumentCollections($documents, $documentsITUser, $documentsBorrow)->sortByDesc('created_at');
        $action = 'approve';

        return view('admin.it.list', compact('documents', 'action'));
    }

    public function adminNewDocuments(Request $request): View
    {
        $filters = $this->resolveNewDocumentsFilters($request);
        $documents = $this->buildFilteredNewDocuments($filters);
        $typeCounts = [
            'IT' => $documents->filter(fn ($document): bool => $document instanceof DocumentIT)->count(),
            'USER' => $documents->filter(fn ($document): bool => $document instanceof DocumentItUser)->count(),
        ];
        $departments = User::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
        $action = 'new';

        return view('admin.it.list', [
            'documents' => $documents,
            'action' => $action,
            'search' => $filters['search'],
            'type' => $filters['type'],
            'subtype' => $filters['subtype'],
            'documentSubtypes' => $filters['documentSubtypes'],
            'department' => $filters['department'],
            'departments' => $departments,
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'typeCounts' => $typeCounts,
        ]);
    }

    public function adminMyDocuments(Request $request): View
    {
        $filters = $this->resolveMyDocumentsFilters($request);
        $documents = $this->buildFilteredMyDocuments($filters, auth()->user()->userid);
        $typeCounts = [
            'IT' => $documents->filter(fn ($document): bool => $document instanceof DocumentIT)->count(),
            'USER' => $documents->filter(fn ($document): bool => $document instanceof DocumentItUser)->count(),
        ];
        $departments = User::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
        $action = 'my';

        return view('admin.it.list', [
            'documents' => $documents,
            'action' => $action,
            'search' => $filters['search'],
            'type' => $filters['type'],
            'subtype' => $filters['subtype'],
            'status' => $filters['status'],
            'documentSubtypes' => $filters['documentSubtypes'],
            'department' => $filters['department'],
            'departments' => $departments,
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'typeCounts' => $typeCounts,
        ]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function documentSubtypes(): array
    {
        return [
            'IT' => [
                'HARDWARE' => 'HARDWARE',
                'SOFTWARE' => 'SOFTWARE',
                'SSB' => 'SSB',
                'HIS' => 'HIS (MonkeyTech)',
                'ERP' => 'ERP (NetSuite)',
                'RESET_PASSWORD' => 'Reset Password',
                'OTHER' => 'อื่นๆ',
            ],
            'USER' => [
                'ขอแก้ไขสิทธิการใช้งาน' => 'ขอแก้ไขสิทธิการใช้งาน',
                'เลขาแพทย์' => 'เลขาแพทย์',
                'ฝ่ายบุคคล' => 'ฝ่ายบุคคล',
            ],
            'BORROW' => [
                'Notebook' => 'Notebook',
                'Computer' => 'Computer',
                'Printer' => 'Printer',
                'Projector' => 'Projector',
                'Ipad/Tablet' => 'Ipad/Tablet',
                'OTHER' => 'อื่นๆ',
            ],
        ];
    }

    public function adminAllDocuments(Request $request): View
    {
        $filters = $this->resolveAllDocumentsFilters($request);
        $documents = $this->buildFilteredAllDocuments($filters);
        $typeCounts = [
            'IT' => $documents->filter(fn ($document): bool => $document instanceof DocumentIT)->count(),
            'USER' => $documents->filter(fn ($document): bool => $document instanceof DocumentItUser)->count(),
            'BORROW' => $documents->filter(fn ($document): bool => $document instanceof DocumentBorrow)->count(),
        ];
        $action = 'all';
        $perPage = filled($filters['process_userid']) || filled($filters['process_log']) ? 100 : 10;
        $documents = $this->workflow->paginateCollection($documents, $perPage, $request);
        $departments = User::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
        $processUsers = User::query()
            ->whereIn('role', ['admin', 'it', 'it-hardware', 'it-approve', 'it-hardware-approve'])
            ->orderBy('userid')
            ->get(['userid', 'name']);

        return view('admin.it.list', [
            'documents' => $documents,
            'action' => $action,
            'search' => $filters['search'],
            'type' => $filters['type'],
            'subtype' => $filters['subtype'],
            'documentSubtypes' => $filters['documentSubtypes'],
            'status' => $filters['status'],
            'department' => $filters['department'],
            'departments' => $departments,
            'process_userid' => $filters['process_userid'],
            'processUsers' => $processUsers,
            'process_log' => $filters['process_log'],
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'typeCounts' => $typeCounts,
        ]);
    }

    public function exportAllDocuments(Request $request): StreamedResponse
    {
        $filters = $this->resolveAllDocumentsFilters($request);
        $documents = $this->buildFilteredAllDocuments($filters);
        $rows = [$this->allDocumentsExportHeaders()];

        foreach ($documents as $document) {
            $rows[] = $this->buildAllDocumentsExportRow($document);
        }

        $exporter = new HisLogExcelExporter;
        $filename = 'it-all-documents-'.now()->format('Y-m-d_His').'.xlsx';

        return response()->streamDownload(
            static function () use ($exporter, $rows): void {
                echo $exporter->buildSheets([
                    'All Documents' => $rows,
                ]);
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    /**
     * @return array{
     *     search: mixed,
     *     status: mixed,
     *     type: string,
     *     subtype: mixed,
     *     department: mixed,
     *     process_userid: mixed,
     *     process_log: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }
     */
    private function resolveAllDocumentsFilters(Request $request): array
    {
        $type = $request->get('type') ?: 'ALL';
        $subtype = $request->get('subtype');
        $documentSubtypes = $this->documentSubtypes();

        if ($type === 'ALL' || ! isset($documentSubtypes[$type][$subtype])) {
            $subtype = null;
        }

        return [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'type' => $type,
            'subtype' => $subtype,
            'department' => $request->get('department'),
            'process_userid' => $request->get('process_userid'),
            'process_log' => $request->get('process_log'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'documentSubtypes' => $documentSubtypes,
        ];
    }

    /**
     * @param  array{
     *     search: mixed,
     *     status: mixed,
     *     type: string,
     *     subtype: mixed,
     *     department: mixed,
     *     process_userid: mixed,
     *     process_log: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }  $filters
     * @return Collection<int, DocumentIT|DocumentItUser|DocumentBorrow>
     */
    private function buildFilteredAllDocuments(array $filters): Collection
    {
        [
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'subtype' => $subtype,
            'department' => $department,
            'process_userid' => $process_userid,
            'process_log' => $process_log,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ] = $filters;

        $itWith = ['creator', 'approvers.user', 'logs' => fn ($query) => $query->where('action', 'process')->orderBy('created_at'), 'logs.user'];
        $itUserWith = ['documentUser.creator', 'approvers.user', 'logs' => fn ($query) => $query->where('action', 'process')->orderBy('created_at'), 'logs.user'];
        $borrowWith = ['creator', 'approvers.user', 'logs' => fn ($query) => $query->where('action', 'process')->orderBy('created_at'), 'logs.user'];

        $itQuery = $this->chatAwareItQuery()->with($itWith);
        if ($search) {
            $itQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%");
            });
        }
        if ($status) {
            $itQuery->where('status', $status);
        }
        if ($department) {
            $itQuery->whereHas('creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }
        $this->filterByProcessLogs($itQuery, $process_userid, $process_log);
        if ($start_date) {
            $itQuery->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $itQuery->whereDate('created_at', '<=', $end_date);
        }
        if ($subtype && $type === 'IT') {
            $this->applyItSubtypeFilter($itQuery, $subtype);
        }
        $documents = ($type == 'ALL' || $type == 'IT') ? $itQuery->get() : collect();

        $itUserQuery = $this->chatAwareItUserQuery()->with($itUserWith);
        if ($search) {
            $itUserQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('documentUser', function ($sq) use ($search) {
                        $sq->where('title', 'LIKE', "%{$search}%")
                            ->orWhere('detail', 'LIKE', "%{$search}%");
                    });
            });
        }
        if ($status) {
            $itUserQuery->where('status', $status);
        }
        if ($department) {
            $itUserQuery->whereHas('documentUser.creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }
        $this->filterByProcessLogs($itUserQuery, $process_userid, $process_log);
        if ($start_date) {
            $itUserQuery->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $itUserQuery->whereDate('created_at', '<=', $end_date);
        }
        if ($subtype && $type === 'USER') {
            $itUserQuery->whereHas('documentUser', function ($q) use ($subtype): void {
                $q->where('title', $subtype);
            });
        }
        $documentsITUser = ($type == 'ALL' || $type == 'USER') ? $itUserQuery->get() : collect();

        $borrowQuery = DocumentBorrow::query()->with($borrowWith);
        if ($search) {
            $borrowQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%");
            });
        }
        if ($status) {
            $borrowQuery->where('status', $status);
        }
        if ($department) {
            $borrowQuery->whereHas('creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }
        $this->filterByProcessLogs($borrowQuery, $process_userid, $process_log);
        if ($start_date) {
            $borrowQuery->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $borrowQuery->whereDate('created_at', '<=', $end_date);
        }
        if ($subtype && $type === 'BORROW') {
            $this->applyBorrowSubtypeFilter($borrowQuery, $subtype);
        }
        $documentsBorrow = ($type == 'ALL' || $type == 'BORROW') ? $borrowQuery->get() : collect();

        $documents = $this->mergeDocumentCollections($documents, $documentsITUser, $documentsBorrow);

        return $this->sortAllDocuments($documents, $process_userid, $process_log);
    }

    /**
     * @return array{
     *     search: mixed,
     *     type: string,
     *     subtype: mixed,
     *     department: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }
     */
    private function resolveNewDocumentsFilters(Request $request): array
    {
        $type = $request->get('type') ?: 'ALL';
        $subtype = $request->get('subtype');
        $documentSubtypes = $this->documentSubtypes();

        if (! in_array($type, ['ALL', 'IT', 'USER'], true)) {
            $type = 'ALL';
        }

        if ($type === 'ALL' || ! isset($documentSubtypes[$type][$subtype])) {
            $subtype = null;
        }

        return [
            'search' => $request->get('search'),
            'type' => $type,
            'subtype' => $subtype,
            'department' => $request->get('department'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'documentSubtypes' => $documentSubtypes,
        ];
    }

    /**
     * @param  array{
     *     search: mixed,
     *     type: string,
     *     subtype: mixed,
     *     department: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }  $filters
     * @return Collection<int, DocumentIT|DocumentItUser>
     */
    private function buildFilteredNewDocuments(array $filters): Collection
    {
        [
            'search' => $search,
            'type' => $type,
            'subtype' => $subtype,
            'department' => $department,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ] = $filters;

        $excludeHardwareSupportTask = function (Builder $query): void {
            $query->where('step', 2)->where('task_user', 'IT Unit Support');
        };

        $itQuery = $this->chatAwareItQuery()
            ->with(['creator', 'approvers.user'])
            ->where('status', 'pending')
            ->whereNull('assigned_user_id')
            ->whereDoesntHave('tasks', $excludeHardwareSupportTask);

        if ($search) {
            $itQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%");
            });
        }

        if ($department) {
            $itQuery->whereHas('creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($start_date) {
            $itQuery->whereDate('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $itQuery->whereDate('created_at', '<=', $end_date);
        }

        if ($subtype && $type === 'IT') {
            $this->applyItSubtypeFilter($itQuery, $subtype);
        }

        $documents = ($type === 'ALL' || $type === 'IT') ? $itQuery->get() : collect();

        $itUserQuery = $this->chatAwareItUserQuery()
            ->with(['documentUser.creator', 'approvers.user'])
            ->where('status', 'pending')
            ->whereNull('assigned_user_id')
            ->whereDoesntHave('tasks', $excludeHardwareSupportTask);

        if ($search) {
            $itUserQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('documentUser', function ($sq) use ($search) {
                        $sq->where('title', 'LIKE', "%{$search}%")
                            ->orWhere('detail', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($department) {
            $itUserQuery->whereHas('documentUser.creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($start_date) {
            $itUserQuery->whereDate('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $itUserQuery->whereDate('created_at', '<=', $end_date);
        }

        if ($subtype && $type === 'USER') {
            $itUserQuery->whereHas('documentUser', function ($q) use ($subtype): void {
                $q->where('title', $subtype);
            });
        }

        $documentsITUser = ($type === 'ALL' || $type === 'USER') ? $itUserQuery->get() : collect();

        return $this->mergeDocumentCollections($documents, $documentsITUser)->sortBy('created_at')->values();
    }

    /**
     * @return array{
     *     search: mixed,
     *     type: string,
     *     subtype: mixed,
     *     status: mixed,
     *     department: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }
     */
    private function resolveMyDocumentsFilters(Request $request): array
    {
        $type = $request->get('type') ?: 'ALL';
        $subtype = $request->get('subtype');
        $status = $request->get('status');
        $documentSubtypes = $this->documentSubtypes();

        if (! in_array($type, ['ALL', 'IT', 'USER'], true)) {
            $type = 'ALL';
        }

        if ($type === 'ALL' || ! isset($documentSubtypes[$type][$subtype])) {
            $subtype = null;
        }

        if ($status && ! in_array($status, ['pending', 'process'], true)) {
            $status = null;
        }

        return [
            'search' => $request->get('search'),
            'type' => $type,
            'subtype' => $subtype,
            'status' => $status,
            'department' => $request->get('department'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'documentSubtypes' => $documentSubtypes,
        ];
    }

    /**
     * @param  array{
     *     search: mixed,
     *     type: string,
     *     subtype: mixed,
     *     status: mixed,
     *     department: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }  $filters
     * @return Collection<int, DocumentIT|DocumentItUser>
     */
    private function buildFilteredMyDocuments(array $filters, string $currentUserId): Collection
    {
        [
            'search' => $search,
            'type' => $type,
            'subtype' => $subtype,
            'status' => $status,
            'department' => $department,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ] = $filters;

        $itQuery = $this->chatAwareItQuery()
            ->with(['creator', 'approvers.user'])
            ->where('assigned_user_id', $currentUserId)
            ->whereIn('status', ['process', 'pending']);

        if ($search) {
            $itQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%");
            });
        }

        if ($status) {
            $itQuery->where('status', $status);
        }

        if ($department) {
            $itQuery->whereHas('creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($start_date) {
            $itQuery->whereDate('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $itQuery->whereDate('created_at', '<=', $end_date);
        }

        if ($subtype && $type === 'IT') {
            $this->applyItSubtypeFilter($itQuery, $subtype);
        }

        $documents = ($type === 'ALL' || $type === 'IT') ? $itQuery->get() : collect();

        $itUserQuery = $this->chatAwareItUserQuery()
            ->with(['documentUser.creator', 'approvers.user'])
            ->where('assigned_user_id', $currentUserId)
            ->whereIn('status', ['process', 'pending']);

        if ($search) {
            $itUserQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('documentUser', function ($sq) use ($search) {
                        $sq->where('title', 'LIKE', "%{$search}%")
                            ->orWhere('detail', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $itUserQuery->where('status', $status);
        }

        if ($department) {
            $itUserQuery->whereHas('documentUser.creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($start_date) {
            $itUserQuery->whereDate('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $itUserQuery->whereDate('created_at', '<=', $end_date);
        }

        if ($subtype && $type === 'USER') {
            $itUserQuery->whereHas('documentUser', function ($q) use ($subtype): void {
                $q->where('title', $subtype);
            });
        }

        $documentsITUser = ($type === 'ALL' || $type === 'USER') ? $itUserQuery->get() : collect();

        return $this->mergeDocumentCollections($documents, $documentsITUser)->sortByDesc('created_at')->values();
    }

    /**
     * @return array{
     *     search: mixed,
     *     subtype: mixed,
     *     status: mixed,
     *     department: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }
     */
    private function resolveBorrowDocumentsFilters(Request $request): array
    {
        $subtype = $request->get('subtype');
        $status = $request->get('status');
        $documentSubtypes = $this->documentSubtypes();

        if (! isset($documentSubtypes['BORROW'][$subtype])) {
            $subtype = null;
        }

        if ($status && ! in_array($status, ['pending', 'borrow', 'return_approve'], true)) {
            $status = null;
        }

        return [
            'search' => $request->get('search'),
            'subtype' => $subtype,
            'status' => $status,
            'department' => $request->get('department'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'documentSubtypes' => $documentSubtypes,
        ];
    }

    /**
     * @param  array{
     *     search: mixed,
     *     subtype: mixed,
     *     status: mixed,
     *     department: mixed,
     *     start_date: mixed,
     *     end_date: mixed,
     *     documentSubtypes: array<string, array<string, string>>
     * }  $filters
     * @return Collection<int, DocumentBorrow>
     */
    private function buildFilteredBorrowDocuments(array $filters): Collection
    {
        [
            'search' => $search,
            'subtype' => $subtype,
            'status' => $status,
            'department' => $department,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ] = $filters;

        $excludeHardwareSupportTask = function (Builder $query): void {
            $query->where('step', 2)->where('task_user', 'IT Unit Support');
        };

        $borrowQuery = DocumentBorrow::query()
            ->with(['creator', 'approvers.user'])
            ->whereIn('status', ['pending', 'borrow', 'return_approve'])
            ->whereDoesntHave('tasks', $excludeHardwareSupportTask);

        if ($search) {
            $borrowQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%");
            });
        }

        if ($status) {
            $borrowQuery->where('status', $status);
        }

        if ($department) {
            $borrowQuery->whereHas('creator', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($start_date) {
            $borrowQuery->whereDate('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $borrowQuery->whereDate('created_at', '<=', $end_date);
        }

        if ($subtype) {
            $this->applyBorrowSubtypeFilter($borrowQuery, $subtype);
        }

        return $borrowQuery->get()->values();
    }

    /**
     * @return list<string>
     */
    private function allDocumentsExportHeaders(): array
    {
        return [
            'เลขที่',
            'ชื่อเอกสาร',
            'รายละเอียด',
            'ผู้ขอ / แผนก / วันที่ขอ',
            'ผู้อนุมัติ',
            'สถานะ',
            'Log No',
            'บันทึกการดำเนินการ',
        ];
    }

    /**
     * @return list<string>
     */
    private function buildAllDocumentsExportRow(DocumentIT|DocumentItUser|DocumentBorrow $document): array
    {
        $creator = $document->creator;
        $createdAt = $document->created_at?->format('d/m/Y H:i:s') ?? '';
        $partitionedLogs = $this->partitionExportProcessLogs($document);

        return [
            $document->document_number ?? '',
            $this->formatExportTitle($document),
            strip_tags((string) ($document->detail ?? '')),
            trim(implode(' / ', array_filter([
                $creator?->name,
                $creator?->department,
                $createdAt,
            ], fn (?string $value): bool => filled($value)))),
            $this->formatExportApprovers($document),
            $this->formatDocumentStatus((string) $document->status),
            $partitionedLogs['log_no'],
            $partitionedLogs['process'],
        ];
    }

    private function formatExportTitle(DocumentIT|DocumentItUser|DocumentBorrow $document): string
    {
        if ($document instanceof DocumentItUser) {
            return (string) ($document->documentUser?->title ?? '');
        }

        $title = $document->title ?? '';

        if (is_array($title)) {
            return implode(' | ', $title);
        }

        return (string) $title;
    }

    private function formatExportApprovers(DocumentIT|DocumentItUser|DocumentBorrow $document): string
    {
        $approvers = $document->approvers ?? collect();

        if ($approvers->isEmpty()) {
            return '';
        }

        return $approvers
            ->map(function ($approver): string {
                $name = $approver->user->name ?? $approver->userid;
                $status = match ($approver->status) {
                    'approve' => 'อนุมัติ',
                    'reject', 'cancel' => 'ไม่อนุมัติ',
                    default => 'รออนุมัติ',
                };

                return "{$name} ({$status})";
            })
            ->implode("\n");
    }

    /**
     * @return array{process: string, log_no: string}
     */
    private function partitionExportProcessLogs(DocumentIT|DocumentItUser|DocumentBorrow $document): array
    {
        $logs = ($document->logs ?? collect())
            ->filter(fn (Log $log): bool => $log->action === 'process');

        $processLogs = [];
        $logNumbers = [];

        foreach ($logs as $log) {
            $formatted = $this->formatExportLogLine($log);

            if (stripos((string) $log->details, 'GOLIVE') !== false) {
                $logNumbers[] = (string) $log->details;

                continue;
            }

            $processLogs[] = $formatted;
        }

        return [
            'process' => implode("\n", $processLogs),
            'log_no' => implode("\n", $logNumbers),
        ];
    }

    private function formatExportLogLine(Log $log): string
    {
        $timestamp = $log->created_at?->format('d/m/Y H:i:s') ?? '';
        $name = $log->user->name ?? $log->userid ?? '-';

        return "[{$timestamp}] {$name}: {$log->details}";
    }

    private function formatDocumentStatus(string $status): string
    {
        return match ($status) {
            'wait_approval' => 'รออนุมัติจากหัวหน้าแผนก',
            'not_approval' => 'หน่วยงานไม่อนุมัติ',
            'cancel' => 'ผู้ขอยกเลิกเอกสาร',
            'pending' => 'รอการดำเนินการ',
            'reject' => 'ยกเลิกเอกสาร',
            'process' => 'กำลังดำเนินการ',
            'done' => 'เอกสารรออนุมัติ',
            'complete' => 'เอกสารเสร็จสมบูรณ์',
            'borrow_approve' => 'รออนุมัติการยืมอุปกรณ์',
            'borrow' => 'อุปกรณ์อยู่ระหว่างการยืม',
            'return_approve' => 'รอรับอุปกรณ์คืน',
            'return' => 'รออนุมัติการคืนอุปกรณ์',
            default => $status,
        };
    }

    private function applyItSubtypeFilter(Builder $query, string $subtype): void
    {
        if ($subtype === 'OTHER') {
            $knownSubtypes = ['HARDWARE', 'SOFTWARE', 'SSB', 'HIS', 'ERP', 'RESET_PASSWORD'];

            $query->where(function (Builder $q) use ($knownSubtypes): void {
                foreach ($knownSubtypes as $knownSubtype) {
                    $q->where('title', 'NOT LIKE', $knownSubtype.'%');
                }
            });

            return;
        }

        if ($subtype === 'RESET_PASSWORD') {
            $query->where('title', 'RESET_PASSWORD');

            return;
        }

        $query->where(function (Builder $q) use ($subtype): void {
            $q->where('title', 'LIKE', $subtype.'|%')
                ->orWhere('title', $subtype);
        });
    }

    private function applyBorrowSubtypeFilter(Builder $query, string $subtype): void
    {
        $knownSubtypes = ['Notebook', 'Computer', 'Printer', 'Projector', 'Ipad/Tablet'];

        if ($subtype === 'OTHER') {
            $query->where(function (Builder $q) use ($knownSubtypes): void {
                $q->whereNull('title')
                    ->orWhereNotIn('title', $knownSubtypes);
            });

            return;
        }

        $query->where('title', $subtype);
    }

    /**
     * @param  Collection<int|string, mixed>  ...$documentGroups
     * @return Collection<int, mixed>
     */
    private function mergeDocumentCollections(Collection ...$documentGroups): Collection
    {
        $merged = collect();

        foreach ($documentGroups as $documents) {
            foreach ($documents as $document) {
                $merged->push($document);
            }
        }

        return $merged->values();
    }

    /**
     * @param  Collection<int, mixed>  $documents
     * @return Collection<int, mixed>
     */
    private function sortAllDocuments(Collection $documents, ?string $processUserid, ?string $processLog = null): Collection
    {
        if (filled($processUserid) || filled($processLog)) {
            return $documents
                ->sortByDesc(fn ($document): int => $this->documentProcessSortTimestamp($document))
                ->values();
        }

        return $documents->sortByDesc('created_at')->values();
    }

    private function documentProcessSortTimestamp(mixed $document): int
    {
        $value = $document->last_process_at ?? $document->created_at;

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (blank($value)) {
            return 0;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? 0 : $timestamp;
    }

    private function filterByProcessLogs(Builder $query, ?string $processUserid, ?string $processLog): void
    {
        if (blank($processUserid) && blank($processLog)) {
            return;
        }

        $constrainProcessLogs = function (Builder $logsQuery) use ($processUserid, $processLog): void {
            $logsQuery->where('action', 'process');

            if (filled($processUserid)) {
                $logsQuery->where('userid', $processUserid);
            }

            if (filled($processLog)) {
                $logsQuery->where('details', 'LIKE', "%{$processLog}%");
            }
        };

        $query->whereHas('logs', $constrainProcessLogs)
            ->withMax(['logs as last_process_at' => $constrainProcessLogs], 'created_at');
    }

    public function adminviewDocument(string $type, int|string $document_id, string $action): View
    {
        if ($type == 'IT') {
            $document = DocumentIT::with('messages')->find($document_id);
        } elseif ($type == 'USER') {
            $document = DocumentItUser::with('messages')->find($document_id);
        } elseif ($type == 'BORROW') {
            $document = DocumentBorrow::find($document_id);
        }

        $userList = [];
        if ($action == 'my') {
            $userList = User::whereIn('role', ['admin', 'it', 'it-hardware', 'it-approve', 'it-hardware-approve'])->get();
        }

        return view('admin.it.view', compact('document', 'action', 'userList', 'type'));
    }

    public function approveHardwareDocument(Request $request): JsonResponse
    {
        $detail = ($request->status == 'approve') ? 'อนุมัติ' : 'ปฏิเสธ '.$request->reason;

        switch ($request->type) {
            case 'IT':
                $document = DocumentIT::find($request->id);
                break;
            case 'BORROW':
                $document = DocumentBorrow::find($request->id);
                break;
        }

        $status = ($request->status == 'approve') ? 'อนุมัติ' : 'ปฏิเสธ';
        if ($request->status == 'approve') {
            $document->status = filled($document->assigned_user_id) ? 'process' : 'pending';
        } else {
            $document->status = 'reject';
        }
        $document->save();

        $document->tasks()->where('step', 2)->update([
            'status' => $request->status,
            'task_name' => $status,
            'task_user' => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => $request->status,
            'details' => $detail,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $detail,
        ]);
    }

    public function acceptDocument(Request $request): JsonResponse
    {
        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } else {
            $document = DocumentItUser::find($request->id);
        }

        if (! $document) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบเอกสาร!',
            ]);
        }

        $currentUserId = auth()->user()->userid;
        $assignedUserId = $document->assigned_user_id;

        if (filled($assignedUserId) && $assignedUserId !== $currentUserId) {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ได้ถูกรับงานแล้ว!',
            ]);
        }

        if ($document->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถรับงานได้!',
            ]);
        }

        $document->status = 'process';
        $document->assigned_user_id = $currentUserId;
        $document->save();

        $document->logs()->create([
            'userid' => $currentUserId,
            'action' => 'accept',
            'details' => 'รับงาน',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'รับงานสำเร็จ!',
        ]);
    }

    public function cancelDocument(Request $request): JsonResponse
    {
        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
            $taskUsers = ['IT Department'];
        } elseif ($request->type == 'BORROW') {
            $document = DocumentBorrow::find($request->id);
            $taskUsers = ['IT Department', 'IT Unit Support'];
        } else {
            $document = DocumentItUser::find($request->id);
            $taskUsers = ['IT Department'];
        }

        if (! $document) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบเอกสาร!',
            ]);
        }

        $document->status = 'reject';
        $document->save();

        $document->tasks()->whereIn('task_user', $taskUsers)->update([
            'status' => 'reject',
            'task_name' => 'ปฏิเสธ',
            'task_user' => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);
        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'cancel',
            'details' => 'ยกเลิกเอกสาร : '.$request->reason,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ยกเลิกเอกสารสำเร็จ!',
        ]);
    }

    public function cancelJob(Request $request): JsonResponse
    {
        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } else {
            $document = DocumentItUser::find($request->id);
        }

        if ($document->status !== 'process' || $document->assigned_user_id !== auth()->user()->userid) {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถยกเลิกงานได้!',
            ]);
        }

        $document->status = 'pending';
        $document->assigned_user_id = null;
        $document->save();
        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'transfer',
            'details' => 'ยกเลิกการรับงาน ส่งใบงานไปยังใบงานใหม่',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ยกเลิกการรับงานสำเร็จ!',
        ]);
    }

    public function processDocument(Request $request): RedirectResponse
    {
        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } else {
            $document = DocumentItUser::find($request->id);
        }

        if ($request->detail !== null) {
            $uploadedFiles = $request->file('document_files');
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $file) {
                    $originalFilename = 'IT_'.$file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $size = $file->getSize();
                    $storedPath = $file->store('uploads', 'public');

                    $document->files()->create([
                        'original_filename' => $originalFilename,
                        'stored_path' => $storedPath,
                        'mime_type' => $mimeType,
                        'size' => $size,
                    ]);
                }
            }

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'process',
                'details' => $request->detail,
            ]);
        }

        if ($request->transfer_userid == null) {
            if ($request->detail === null) {
                return redirect()->route('admin.it.mylist')->with('error', 'กรุณากรอกรายละเอียดการดำเนินการ!');
            }
            $document->status = 'done';
            $document->assigned_user_id = null;
            $document->save();
            $document->tasks()->where('task_user', 'IT Department')->update([
                'status' => 'approve',
                'task_name' => 'ดำเนินการเสร็จสิ้น',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($request->transfer_userid === 'new') {
            if ($request->detail === null) {
                return redirect()->route('admin.it.mylist')->with('error', 'กรุณากรอกรายละเอียดการดำเนินการ!');
            }

            $document->status = 'pending';
            $document->assigned_user_id = null;
            $document->save();
            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'work',
                'details' => 'ดำเนินการเสร็จสิ้น ส่งใบงานไปยังใบงานใหม่',
            ]);
        } else {
            $document->status = 'process';
            $document->assigned_user_id = $request->transfer_userid;
            $document->save();
            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'transfer',
                'details' => 'ส่งใบงานไปยัง '.$request->transfer_userid,
            ]);
        }

        return redirect()->route('admin.it.mylist')->with('success', 'ดำเนินการสำเร็จ!');
    }

    public function completeDocument(Request $request): JsonResponse
    {
        if ($request->type == 'IT') {
            $document = DocumentIT::find($request->id);
        } elseif ($request->type == 'USER') {
            $document = DocumentItUser::find($request->id);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบเอกสาร!',
            ]);
        }

        if ($document->status !== 'done') {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถดำเนินการได้!',
            ]);
        }

        if ($request->status === 'approve') {
            $document->status = 'complete';
            $document->save();

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'complete',
                'details' => 'อนุมัติเอกสารเสร็จสิ้น',
            ]);

            $document->tasks()->orderBy('step', 'desc')->first()->update([
                'status' => 'approve',
                'task_name' => 'อนุมัติเอกสารเสร็จสิ้น',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $document->status = 'pending';
            $document->save();

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'reject',
                'details' => 'ไม่อนุมัติเอกสาร : '.$request->reason,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'อนุมัติเอกสารเสร็จสิ้น!',
        ]);
    }

    public function completeAllDocument(): JsonResponse
    {
        $documents = DocumentIT::where('status', 'done')->get();
        foreach ($documents as $document) {
            $document->status = 'complete';
            $document->save();

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'complete',
                'details' => 'อนุมัติเอกสารเสร็จสิ้น',
            ]);

            $document->tasks()->orderBy('step', 'desc')->first()->update([
                'status' => 'approve',
                'task_name' => 'อนุมัติเอกสารเสร็จสิ้น',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);
        }

        $documentUser = DocumentItUser::where('status', 'done')->get();
        foreach ($documentUser as $document) {
            $document->status = 'complete';
            $document->save();

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'complete',
                'details' => 'อนุมัติเอกสารเสร็จสิ้น',
            ]);

            $document->tasks()->orderBy('step', 'desc')->first()->update([
                'status' => 'approve',
                'task_name' => 'อนุมัติเอกสารเสร็จสิ้น',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);
        }

        $documentsBorrow = DocumentBorrow::whereIn('status', ['borrow_approve', 'return'])->get();
        foreach ($documentsBorrow as $borrow) {
            switch ($borrow->status) {
                case 'borrow_approve':
                    $status = 'borrow';
                    $task = 'รออนุมัติการยืม จากฝ่ายเทคโนโลยีสารสนเทศ';
                    $detail = 'อนุมัติการให้ยึมอุปกรณ์';
                    break;
                case 'return':
                    $status = 'complete';
                    $task = 'คืนอุปกรณ์เรียบร้อย';
                    $detail = 'คืนอุปกรณ์เรียบร้อย';
                    break;
            }
            $borrow->status = $status;
            $borrow->save();

            $borrow->tasks()->where('task_name', $task)->update([
                'status' => 'approve',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);

            $borrow->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'approve',
                'details' => $detail,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'อนุมัติเอกสารเสร็จสิ้น!',
        ]);
    }

    public function adminBorrowDocuments(Request $request): View
    {
        $filters = $this->resolveBorrowDocumentsFilters($request);
        $documents = $this->buildFilteredBorrowDocuments($filters);
        $departments = User::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
        $action = 'borrow';

        return view('admin.it.list', [
            'documents' => $documents,
            'action' => $action,
            'search' => $filters['search'],
            'subtype' => $filters['subtype'],
            'status' => $filters['status'],
            'documentSubtypes' => $filters['documentSubtypes'],
            'department' => $filters['department'],
            'departments' => $departments,
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'typeCounts' => ['BORROW' => $documents->count()],
        ]);
    }

    public function adminBorrowAdd(Request $request): JsonResponse
    {
        $document = DocumentBorrow::find($request->id);
        $approver = $document->approvers()->first();
        $document->hardwares()->create([
            'serial_number' => $request->serial,
            'detail' => $request->detail,
            'borrow_date' => $request->date,
            'approver' => $approver->userid,
        ]);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'add',
            'details' => 'ระบุอุปกรณ์ที่ให้ยืม : '.$request->serial,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'เพิ่มอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowRemove(Request $request): JsonResponse
    {
        $document = Hardware::find($request->id);
        $document->borrow_document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'remove',
            'details' => 'ลบอุปกรณ์ที่ให้ยืม : '.$document->serial_number,
        ]);
        $document->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'ลบอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowSummary(Request $request): JsonResponse
    {
        $document = DocumentBorrow::find($request->id);

        if (count($document->hardwares) == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'ต้องมีอุปกรณ์ที่ยืมอย่างน้อย 1 อุปกรณ์!',
            ]);
        }

        $document->status = 'borrow_approve';
        $document->save();

        $document->tasks()->where('task_name', 'รอบันทึกรายละเอียดการยืม จากฝ่ายเทคโนโลยีสารสนเทศ')->update([
            'status' => 'approve',
            'task_user' => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'process',
            'details' => 'ขออนุมัติการให้ยึมอุปกรณ์',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ขออนุมัติอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowApprove(Request $request): JsonResponse
    {
        switch ($request->type) {
            case 'borrow':
                $status = 'borrow';
                $task = 'รออนุมัติการยืม จากฝ่ายเทคโนโลยีสารสนเทศ';
                $detail = 'อนุมัติการให้ยึมอุปกรณ์';
                break;
            case 'return':
                $status = 'complete';
                $task = 'คืนอุปกรณ์เรียบร้อย';
                $detail = 'อนุมัติการคืนอุปกรณ์';
                break;
        }

        $document = DocumentBorrow::find($request->id);
        $document->status = $status;
        $document->save();

        $document->tasks()->where('task_name', $task)->update([
            'status' => 'approve',
            'task_user' => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date' => date('Y-m-d H:i:s'),
        ]);

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'approve',
            'details' => $detail,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'อนุมัติสำเร็จ!',
        ]);
    }

    public function borrowReturn(Request $request): JsonResponse
    {
        $hardware = Hardware::find($request->id);
        $document = $hardware->borrow_document;

        $hardware->return_date = date('Y-m-d H:i');
        $hardware->save();

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'retrun',
            'details' => 'ขอคืนอุปกรณ์ : '.$hardware->serial_number,
        ]);

        if ($document->hardwares()->whereNull('return_date')->count() == 0) {
            $document->status = 'return_approve';
            $document->save();

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'retrun',
                'details' => 'ขอคืนอุปกรณ์ครบแล้ว',
            ]);

            $document->tasks()->where('task_name', 'รออนุมัติการคืน จากแผนกผู้ขอยืม')->update([
                'status' => 'approve',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'รับอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminBorrowRetrieve(Request $request): JsonResponse
    {
        $hardware = Hardware::find($request->id);
        $document = $hardware->borrow_document;

        $hardware->retrieve_date = date('Y-m-d H:i');
        $hardware->save();

        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => 'retrun',
            'details' => 'รับอุปกรณ์คืน : '.$document->serial_number,
        ]);

        if ($document->hardwares()->whereNull('retrieve_date')->count() == 0) {
            $document->status = 'return';
            $document->save();

            $document->logs()->create([
                'userid' => auth()->user()->userid,
                'action' => 'retreive',
                'details' => 'รับคืนอุปกรณ์ครบแล้ว',
            ]);

            $document->tasks()->where('task_name', 'รอบันทึกรายละเอียดการคืน จากฝ่ายเทคโนโลยีสารสนเทศ')->update([
                'status' => 'approve',
                'task_user' => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date' => date('Y-m-d H:i:s'),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'รับอุปกรณ์สำเร็จ!',
        ]);
    }

    public function adminReportDocuments(Request $request): View
    {
        $start_date = $request->get('start_date', date('Y-m-01'));
        $end_date = $request->get('end_date', date('Y-m-d'));
        $excludedStatuses = ['cancel', 'not_approval'];

        $itDocs = DocumentIT::query()->whereNotIn('status', $excludedStatuses);
        $itUserDocs = DocumentItUser::query()->whereNotIn('status', $excludedStatuses);
        $borrowDocs = DocumentBorrow::query()->whereNotIn('status', $excludedStatuses);

        if ($start_date) {
            $itDocs->whereDate('created_at', '>=', $start_date);
            $itUserDocs->whereDate('created_at', '>=', $start_date);
            $borrowDocs->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $itDocs->whereDate('created_at', '<=', $end_date);
            $itUserDocs->whereDate('created_at', '<=', $end_date);
            $borrowDocs->whereDate('created_at', '<=', $end_date);
        }

        $itDocs = $itDocs->with('creator')->get();
        $itUserDocs = $itUserDocs->with('documentUser.creator')->get();
        $borrowDocs = $borrowDocs->with('creator')->get();

        $deptStats = [];
        foreach ($itDocs as $doc) {
            $dept = $doc->creator->department ?? 'N/A';
            $deptStats[$dept] = ($deptStats[$dept] ?? 0) + 1;
        }
        foreach ($itUserDocs as $doc) {
            $dept = $doc->documentUser->creator->department ?? 'N/A';
            $deptStats[$dept] = ($deptStats[$dept] ?? 0) + 1;
        }
        foreach ($borrowDocs as $doc) {
            $dept = $doc->creator->department ?? 'N/A';
            $deptStats[$dept] = ($deptStats[$dept] ?? 0) + 1;
        }
        arsort($deptStats);

        $codeStats = [];
        foreach (collect($itDocs)->merge($itUserDocs)->merge($borrowDocs) as $doc) {
            $code = strtoupper(substr($doc->document_number ?? '', 0, 3));
            if ($code === '') {
                $code = 'N/A';
            }
            $codeStats[$code] = ($codeStats[$code] ?? 0) + 1;
        }
        arsort($codeStats);

        $logsQuery = Log::whereIn('action', ['accept', 'process', 'transfer', 'work']);
        if ($start_date) {
            $logsQuery->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $logsQuery->whereDate('created_at', '<=', $end_date);
        }
        $logs = $logsQuery->with('user')->get();

        $adminStats = [];
        foreach ($logs as $log) {
            $admin = $log->user->name ?? $log->userid;
            if (! isset($adminStats[$admin])) {
                $adminStats[$admin] = ['take' => 0, 'close' => 0, 'transfer' => 0];
            }
            if ($log->action == 'accept') {
                $adminStats[$admin]['take']++;
            } elseif ($log->action == 'process' || $log->action == 'work') {
                $adminStats[$admin]['close']++;
            } elseif ($log->action == 'transfer') {
                $adminStats[$admin]['transfer']++;
            }
        }

        $keys = ['wait_approval', 'pending', 'process', 'done', 'complete', 'reject', 'total'];
        $itStats = array_fill_keys($keys, 0);
        $userStats = array_fill_keys($keys, 0);
        $borrowStats = array_fill_keys($keys, 0);

        $process = function ($docs, &$stats): void {
            foreach ($docs as $doc) {
                $stats['total']++;
                if ($doc->status == 'wait_approval') {
                    $stats['wait_approval']++;
                } elseif (in_array($doc->status, ['pending', 'return_approve'])) {
                    $stats['pending']++;
                } elseif (in_array($doc->status, ['process', 'borrow'])) {
                    $stats['process']++;
                } elseif (in_array($doc->status, ['done', 'borrow_approve', 'return'])) {
                    $stats['done']++;
                } elseif ($doc->status == 'complete') {
                    $stats['complete']++;
                } elseif (in_array($doc->status, ['reject', 'cancel', 'not_approval'])) {
                    $stats['reject']++;
                }
            }
        };

        $process($itDocs, $itStats);
        $process($itUserDocs, $userStats);
        $process($borrowDocs, $borrowStats);

        $allStats = array_fill_keys($keys, 0);
        foreach ($keys as $key) {
            $allStats[$key] = $itStats[$key] + $userStats[$key] + $borrowStats[$key];
        }

        return view('admin.it.report', compact('deptStats', 'codeStats', 'adminStats', 'allStats', 'itStats', 'userStats', 'borrowStats', 'start_date', 'end_date'));
    }
}
