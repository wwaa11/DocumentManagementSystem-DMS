<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateApproverRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\Admin\ApproverAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private ApproverAdminService $approverAdminService) {}

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
        $this->approverAdminService->updateApprover($request->validated());

        return redirect()->back()->with('success', 'Approver updated successfully!');
    }

    public function RoleList(Request $request): View
    {
        $data = $this->approverAdminService->listRoles($request->input('search'));

        return view('admin.roles')->with($data);
    }

    public function RoleUpdate(UpdateRoleRequest $request): RedirectResponse
    {
        try {
            $this->approverAdminService->updateRole($request->validated());
        } catch (\InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['role' => $exception->getMessage()]);
        }

        return redirect()->back()->with('success', 'Role updated successfully!');
    }
}
