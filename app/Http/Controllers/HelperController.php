<?php
namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\DocumentListApprover;
use App\Models\DocumentListTask;
use App\Models\File;
use App\Models\Log;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    public function createApprover($type, $dataField, $approveable)
    {
        $approverGetList = DocumentListApprover::where('document_type', $type)->orderBy('step', 'asc')->get();
        $approverList    = [];
        foreach ($approverGetList as $approver) {
            if ($approver->userid == 'head_of_department') {
                if ($dataField['selfApprove'] == 'true') {
                    $approverList[] = new Approver([
                        'userid'      => auth()->user()->userid,
                        'step'        => $approver->step,
                        'status'      => 'Approve',
                        'approved_at' => date('Y-m-d H:i:s'),
                    ]);
                    $approveable->status = 'pending';
                    $approveable->save();
                } else {
                    if ($dataField['approver']['userid'] == auth()->user()->userid) {
                        $approverList[] = new Approver([
                            'userid'      => $dataField['approver']['userid'],
                            'step'        => $approver->step,
                            'status'      => 'Approve',
                            'approved_at' => date('Y-m-d H:i:s'),
                        ]);
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

        $approveable->approvers()->saveMany($approverList);
    }

    public function createTask($documentType, $taskable)
    {
        $taskList = DocumentListTask::where('document_type', $documentType)->orderBy('step', 'asc')->get();
        foreach ($taskList as $task) {
            $taskable->tasks()->create([
                'step' => $task->step,
            ]);
        }
    }

    public function createFile(Request $request, $fileable)
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

    public function createLog($log, $logable)
    {
        $logData = new Log([
            'userid'  => auth()->user()->userid,
            'action'  => $log['action'],
            'details' => $log['details'],
        ]);

        $logable->logs()->save($logData);
    }
}
