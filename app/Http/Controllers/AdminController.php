<?php
namespace App\Http\Controllers;

use DB;

class AdminController extends Controller
{
    public function ApproverList()
    {
        // 1. Get simple list for a dropdown or filter (if needed)
        $depts = DB::connection('staff')
            ->table('departments')
            ->where('department', '!=', 'Doctor')
            ->orderBy('department', 'asc')
            ->pluck('department', 'id');
        // 2. Optimized Main Query: One query to get everything
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
}
