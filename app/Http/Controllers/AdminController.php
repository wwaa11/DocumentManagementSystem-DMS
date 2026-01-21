<?php
namespace App\Http\Controllers;

use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    public function ApproverList()
    {
        $depts = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department', 'asc')
            ->pluck('department', 'id');

        $datas = DB::connection('staff')
            ->table('departments')
            ->leftJoin('approvers', 'departments.id', '=', 'approvers.department_id')
            ->leftJoin('users', 'approvers.userid', '=', 'users.userid')
            ->leftJoin('emails', 'users.userid', '=', 'emails.userid')
            ->where('departments.department', '!=', 'Doctor')
            ->where('approvers.level', 1)
            ->select(
                'departments.id',
                'departments.department',
                'departments.updated_at as dept_last_update',
                'approvers.userid',
                'approvers.updated_at as last_update',
                'approvers.updated_userid as last_userid',
                'approvers.updated_username as last_username',
                'users.name',
                'emails.email',
                'users.position'
            )
            ->orderBy('departments.department', 'asc')
            ->get();
        $err_list = [];

        foreach ($datas as $data) {
            // Check if user data is missing or invalid
            if (empty($data->userid) || $data->userid === '-') {
                $err_list[] = $data->department;

                // Set default "not found" values
                $data->name          = 'ไม่พบข้อมูล';
                $data->email         = 'ไม่พบข้อมูล';
                $data->position      = 'ไม่พบข้อมูล';
                $data->last_update   = null;
                $data->last_userid   = null;
                $data->last_username = null;
            }
        }

        $noti = [
            'count'    => $datas->count(),
            'error'    => count($err_list),
            'err_list' => $err_list,
        ];

        return view('admin.approvers')->with(compact('depts', 'datas', 'noti'));
    }

    public function ApproverGetUser(Request $request)
    {
        $userid = $request->input('userid');

        $response = Http::withHeaders(['token' => env('API_AUTH_KEY')])
            ->timeout(30)
            ->post('http://172.20.1.12/dbstaff/api/getuser', [
                'userid' => $userid,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch user data'], 500);
        }

        $userData = $response->json();
        $response = [
            'success' => true,
            'user'    => $userData['user'],
        ];

        return response()->json($response);
    }

    public function ApproverUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'department' => 'required|string',
            'userid'     => 'required|string|max:255',
            'name'       => 'required|string|max:255',
            'position'   => 'required|string|max:255',
            'email'      => 'required|string|max:255',
        ]);

        $depts = DB::connection('staff')
            ->table('departments')
            ->where('department', $request->input('department'))
            ->orderBy('department', 'asc')
            ->first();

        // Update the approver record
        DB::connection('staff')
            ->table('approvers')
            ->where('department_id', $depts->id)
            ->where('level', 1)
            ->update([
                'userid'           => $validatedData['userid'],
                'updated_at'       => date('Y-m-d H:i:s'),
                'updated_userid'   => auth()->user()->userid,
                'updated_username' => auth()->user()->name,
            ]);

        return redirect()->back()->with('success', 'Approver updated successfully!');
    }

    public function RoleList(Request $request)
    {
        $search = $request->input('search');

        $roles = [
            'admin'               => 'Admin',
            'it'                  => 'IT',
            'it-hardware'         => 'IT Hardware',
            'it-approve'          => 'IT Approve',
            'lab'                 => 'Lab',
            'lab-approve'         => 'Lab Approve',
            'pac'                 => 'PAC',
            'pac-approve'         => 'PAC Approve',
            'heartstream'         => 'Heartstream',
            'heartstream-approve' => 'Heartstream Approve',
            'register'            => 'Register',
            'register-approve'    => 'Register Approve',
        ];

        $users = User::where('role', '!=', 'dev')
            ->where(function ($query) use ($search) {
                $query->where('userid', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
            })
            ->orderBy('userid', 'asc')
            ->paginate(100);

        return view('admin.roles')->with(compact('users', 'roles', 'search'));
    }

    public function RoleUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'userid' => 'required|string|max:255',
            'role'   => 'required|string|max:255',
        ]);

        User::where('userid', $validatedData['userid'])
            ->update([
                'role' => $validatedData['role'],
            ]);

        return redirect()->back()->with('success', 'Role updated successfully!');
    }
}
