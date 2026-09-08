<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DocumentViewPermissionService
{
    public function __construct(private ApproverAdminService $approverAdminService) {}

    /**
     * @return array{users: \Illuminate\Support\Collection<int, User>, departments: list<string>, search: ?string, apiNotice: ?array{status: string, message: ?string}}
     */
    public function listPermissionUsers(?string $search = null): array
    {
        $search = filled($search) ? trim($search) : null;
        $apiNotice = null;

        $users = User::query()
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('userid', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            }, function ($query): void {
                $query->where('can_view_department_documents', true);
            })
            ->orderByDesc('can_view_department_documents')
            ->orderBy('name')
            ->get();

        if ($users->isEmpty() && filled($search) && preg_match('/^[A-Za-z0-9_-]{3,20}$/', $search)) {
            $existing = User::query()->where('userid', $search)->first();

            if ($existing) {
                $users = collect([$existing]);
            } else {
                $import = $this->approverAdminService->importUserFromStaff($search);
                $apiNotice = [
                    'status' => $import['status'],
                    'message' => $import['message'],
                ];

                if ($import['status'] === 'imported' && $import['user'] instanceof User) {
                    $users = collect([$import['user']]);
                }
            }
        }

        $departments = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department')
            ->pluck('department')
            ->all();

        return [
            'users' => $users,
            'departments' => $departments,
            'search' => $search,
            'apiNotice' => $apiNotice,
        ];
    }

    /**
     * @param  array{userid: string, can_view_department_documents: bool, view_departments?: list<string>|null}  $validated
     */
    public function updateUserViewPermission(array $validated): User
    {
        $user = User::query()->where('userid', $validated['userid'])->firstOrFail();

        $canView = (bool) $validated['can_view_department_documents'];
        $departments = $canView
            ? array_values(array_unique(array_filter($validated['view_departments'] ?? [])))
            : [];

        if ($canView && $departments === []) {
            throw new InvalidArgumentException('กรุณาเลือกแผนกที่สามารถดูเอกสารได้อย่างน้อย 1 แผนก');
        }

        $user->update([
            'can_view_department_documents' => $canView,
            'view_departments' => $departments === [] ? null : $departments,
        ]);

        return $user->fresh();
    }
}
