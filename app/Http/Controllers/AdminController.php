<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateApproverRequest;
use App\Http\Requests\Admin\UpdateDocumentViewPermissionRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\Admin\ApproverAdminService;
use App\Services\Admin\DocumentViewPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminController extends Controller
{
    public function __construct(
        private ApproverAdminService $approverAdminService,
        private DocumentViewPermissionService $documentViewPermissionService,
    ) {}

    public function ApproverList(): View
    {
        $data = $this->approverAdminService->listApprovers();

        return view('admin.approvers')->with($data);
    }

    public function ApproverGetUser(Request $request): JsonResponse
    {
        $result = $this->approverAdminService->fetchUser((string) $request->input('userid'));

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json([
            'success' => true,
            'user' => $result['user'],
        ]);
    }

    public function ApproverUpdate(UpdateApproverRequest $request): RedirectResponse
    {
        try {
            $this->approverAdminService->updateApprover($request->validated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['department' => $exception->getMessage()]);
        }

        return redirect()->back()->with('success', 'Approver updated successfully!');
    }

    public function RoleList(Request $request): View
    {
        $data = $this->approverAdminService->listRoles(
            $request->input('search'),
            $request->input('filter'),
            $request->input('role')
        );

        return view('admin.roles')->with($data);
    }

    public function RoleUpdate(UpdateRoleRequest $request): RedirectResponse
    {
        try {
            $this->approverAdminService->updateRole($request->validated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['role' => $exception->getMessage()]);
        }

        return redirect()->back()->with('success', 'Role updated successfully!');
    }

    public function updateDocumentViewPermission(UpdateDocumentViewPermissionRequest $request): RedirectResponse
    {
        try {
            $this->documentViewPermissionService->updateUserViewPermission($request->validated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['view_departments' => $exception->getMessage()]);
        }

        return redirect()->back()->with('success', 'อัปเดตสิทธิ์ดูเอกสารแผนกสำเร็จ');
    }
}
