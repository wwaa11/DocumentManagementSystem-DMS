<?php
namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\DocumentListApprover;
use App\Models\DocumentListTask;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HelperController extends Controller
{
    public function paginateCollection($items, $perPage = 15, $queryInput = [])
    {
        if (is_array($items)) {
            $items = new Collection($items);
        }

        $queryParameters = [];
        if ($queryInput instanceof Request) {
            $queryParameters = $queryInput->query();
        } else {
            $queryParameters = $queryInput;

        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total       = $items->count();

        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator($currentItems, $total, $perPage, $currentPage, [
            'path'  => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $queryParameters,
        ]);
    }

    public function createApprover($type, $dataField, $approveable)
    {
        $approverGetList = DocumentListApprover::where('document_type', $type)->orderBy('step', 'asc')->get();
        $approverList    = [];
        foreach ($approverGetList as $approver) {
            if ($approver->userid == 'head_of_department') {
                $islastStep = $approver->step == $approverGetList->count();
                if ($dataField['selfApprove'] == 'true') {
                    $approverList[] = new Approver([
                        'userid'      => auth()->user()->userid,
                        'step'        => $approver->step,
                        'status'      => 'approve',
                        'approved_at' => date('Y-m-d H:i:s'),
                    ]);
                    if ($islastStep && $type !== 'user') {
                        $approveable->status = 'pending';
                        $approveable->save();
                    }
                } else {
                    if ($dataField['approver']['userid'] == auth()->user()->userid) {
                        $approverList[] = new Approver([
                            'userid'      => $dataField['approver']['userid'],
                            'step'        => $approver->step,
                            'status'      => 'approve',
                            'approved_at' => date('Y-m-d H:i:s'),
                        ]);
                        if ($islastStep && $type !== 'user') {
                            $approveable->status = 'pending';
                            $approveable->save();
                        }
                    } else {
                        $approverList[] = new Approver([
                            'userid' => $dataField['approver']['userid'],
                            'step'   => $approver->step,
                        ]);
                    }
                }
            } else {
                $approverList[] = new Approver([
                    'userid' => $approver->userid,
                    'step'   => $approver->step,
                ]);
            }
        }

        // To implement send EMAIL
        // $dataField['approver']['email']

        $approveable->approvers()->saveMany($approverList);
    }

    public function createTask($taskData, $taskable)
    {
        $taskList = DocumentListTask::where('document_type', $taskData['document_type'])->orderBy('step', 'asc')->get();
        foreach ($taskList as $task) {
            $taskAttributes = [
                'step'          => $task->step,
                'task_name'     => $task->task_name,
                'task_user'     => $task->task_user,
                'task_position' => $task->task_position,
            ];
            if (
                ($task->step == 1 && $task->task_user == 'head_of_department' && $taskData['selfApprove']) ||
                ($task->step == 1 && $taskData['approver']['userid'] == auth()->user()->userid)
            ) {
                $taskAttributes['status']        = 'approve';
                $taskAttributes['task_name']     = 'อนุมัติ';
                $taskAttributes['task_user']     = auth()->user()->userid;
                $taskAttributes['task_position'] = auth()->user()->position;
                $taskAttributes['date']          = date('Y-m-d H:i:s');

                $findOtherApprove = null;
                if ($taskable->approvers) {
                    $findOtherApprove = $taskable->approvers()->where('status', 'wait')->first();
                }

                if ($findOtherApprove !== null && $taskable->assigned_user_id !== null) {
                    $taskable->status = 'process';
                    $taskable->save();
                }
            }

            if ($task->task_user == 'head_of_department') {
                $taskAttributes['task_user']     = $taskData['approver']['userid'];
                $taskAttributes['task_position'] = $taskData['approver']['position'];
            }

            $taskable->tasks()->create($taskAttributes);
        }
    }

    public function createFile($request, $fileable)
    {

        $uploadedFiles = $request->file('document_files');

        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                $originalFilename = $file->getClientOriginalName();
                $mimeType         = $file->getMimeType();
                $size             = $file->getSize();
                $storedPath       = $file->store('uploads', 'public'); // Store in storage/app/public/uploads

                $fileData = new File([
                    'original_filename' => $originalFilename,
                    'stored_path'       => $storedPath,
                    'mime_type'         => $mimeType,
                    'size'              => $size,
                ]);

                $fileable->files()->save($fileData);
            }
        }
    }

}
