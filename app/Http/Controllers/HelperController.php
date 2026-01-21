<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\DocumentListApprover;
use App\Models\DocumentListTask;
use App\Models\File;
use App\Models\Mail;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

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
        $total = $items->count();

        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator($currentItems, $total, $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $queryParameters,
        ]);
    }

    public function createApprover($type, $dataField, $approveable)
    {
        $approverGetList = DocumentListApprover::where('document_type', $type)->orderBy('step', 'asc')->get();
        $approverList = [];
        foreach ($approverGetList as $approver) {
            if ($approver->userid == 'head_of_department') {
                $islastStep = $approver->step == $approverGetList->count();
                // Self Approve
                if ($dataField['selfApprove'] == 'true') {
                    // Create Self Approve
                    $approverList[] = new Approver([
                        'userid' => auth()->user()->userid,
                        'step' => $approver->step,
                        'status' => 'approve',
                        'approved_at' => date('Y-m-d H:i:s'),
                    ]);
                    // Check Last Step and Update Document Status
                    if ($islastStep && $type !== 'user') {
                        if ($approveable->assigned_user_id !== null) {
                            $approveable->update([
                                'status' => 'process',
                            ]);
                        } else {
                            $approveable->update([
                                'status' => 'pending',
                            ]);
                        }
                    }
                }
                // Need to Approve
                else {
                    // the Approver is the current user
                    if ($dataField['approver']['userid'] == auth()->user()->userid) {
                        // Create Self Approve
                        $approverList[] = new Approver([
                            'userid' => $dataField['approver']['userid'],
                            'step' => $approver->step,
                            'status' => 'approve',
                            'approved_at' => date('Y-m-d H:i:s'),
                        ]);

                        if ($islastStep && $type !== 'user') {
                            if ($approveable->assigned_user_id !== null) {
                                $approveable->update([
                                    'status' => 'process',
                                ]);
                            } else {
                                $approveable->update([
                                    'status' => 'pending',
                                ]);
                            }
                        }
                    }
                    // Need to Approve
                    else {
                        $approverList[] = new Approver([
                            'userid' => $dataField['approver']['userid'],
                            'step' => $approver->step,
                        ]);

                        $this->mail($dataField['approver']['email'], $approveable);
                    }
                }
            }
            // More than 1 step
            else {
                $approverList[] = new Approver([
                    'userid' => $approver->userid,
                    'step' => $approver->step,
                ]);
            }
        }

        $approveable->approvers()->saveMany($approverList);
    }

    public function createTask($taskData, $taskable)
    {
        $taskList = DocumentListTask::where('document_type', $taskData['document_type'])->orderBy('step', 'asc')->get();
        foreach ($taskList as $task) {
            $taskAttributes = [
                'step' => $task->step,
                'task_name' => $task->task_name,
                'task_user' => $task->task_user,
                'task_position' => $task->task_position,
            ];
            if (
                ($task->step == 1 && $task->task_user == 'head_of_department' && $taskData['selfApprove']) ||
                ($task->step == 1 && $taskData['approver']['userid'] == auth()->user()->userid)
            ) {
                $taskAttributes['status'] = 'approve';
                $taskAttributes['task_name'] = 'อนุมัติ';
                $taskAttributes['task_user'] = auth()->user()->userid;
                $taskAttributes['task_position'] = auth()->user()->position;
                $taskAttributes['date'] = date('Y-m-d H:i:s');

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
                $taskAttributes['task_user'] = $taskData['approver']['userid'];
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
                $mimeType = $file->getMimeType();
                $size = $file->getSize();
                $storedPath = $file->store('uploads', 'public'); // Store in storage/app/public/uploads

                $fileData = new File([
                    'original_filename' => $originalFilename,
                    'stored_path' => $storedPath,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);

                $fileable->files()->save($fileData);
            }
        }
    }

    public function mail($email, $document)
    {
        $title = is_string($document->title) ? $document->title : $document->title[0].$document->title[1];
        $detail = $document->detail;
        $detail .= '<br><br><a href="'.route('document.type.approve', ['document_type' => $document->document_tag['document_tag'], 'document_id' => $document->id]).'">Click here to approve</a>';

        if ($email == '' || $email == '-' || env('APP_ENV') == 'local') {

            $mail = Mail::create([
                'email' => $email,
                'subject' => $title,
                'body' => $detail,
                'status' => 'test',
            ]);

            return true;
        } else {
            $mail = Mail::create([
                'email' => $email,
                'subject' => $title,
                'body' => $detail,
            ]);

            $response = Http::withHeaders([
                'content-type' => 'application/json',
                'API_KEY' => env('API_EMAIL'),
            ])->post('http://172.20.1.12:8086/api/email/local/send', [
                'To' => [$email],
                'Cc' => [],
                'Bcc' => [],
                'Username' => 'pr9autosendmail@praram9.com',
                'Password' => 'P@rnchai289',
                'DisplayName' => 'DMS',
                'Subject' => $document->title,
                'Body' => $detail,
                'Attachments' => [],
            ]);

            $response = $response->json();
            if ($response['responseCode'] == 1) {
                $mail->status = 'success';
                $mail->save();
            } else {
                $mail->status = $response['responseMsg'];
                $mail->save();
            }

            return true;
        }
    }
}
