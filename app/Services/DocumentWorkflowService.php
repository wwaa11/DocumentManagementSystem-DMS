<?php

namespace App\Services;

use App\Models\Approver;
use App\Models\DocumentListApprover;
use App\Models\DocumentListTask;
use App\Models\File;
use App\Models\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class DocumentWorkflowService
{
    public function __construct(private CollectionPaginator $collectionPaginator) {}

    public function paginateCollection(mixed $items, int $perPage = 15, array|Request $queryInput = []): LengthAwarePaginator
    {
        return $this->collectionPaginator->paginate($items, $perPage, $queryInput);
    }

    public function createApprover(string $type, array $dataField, Model $approveable): bool
    {
        $approverGetList = DocumentListApprover::where('document_type', $type)->orderBy('step', 'asc')->get();
        $approverList = [];
        $isApprove = false;

        foreach ($approverGetList as $approver) {
            if ($approver->userid == 'head_of_department') {
                $islastStep = $approver->step == $approverGetList->count();

                if ($dataField['selfApprove'] == 'true') {
                    $approverList[] = new Approver([
                        'userid' => auth()->user()->userid,
                        'step' => $approver->step,
                        'status' => 'approve',
                        'approved_at' => date('Y-m-d H:i:s'),
                    ]);
                    $isApprove = true;

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
                } else {
                    if ($dataField['approver']['userid'] == auth()->user()->userid) {
                        $approverList[] = new Approver([
                            'userid' => $dataField['approver']['userid'],
                            'step' => $approver->step,
                            'status' => 'approve',
                            'approved_at' => date('Y-m-d H:i:s'),
                        ]);
                        $isApprove = true;

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
                    } else {
                        $approverList[] = new Approver([
                            'userid' => $dataField['approver']['userid'],
                            'step' => $approver->step,
                        ]);

                        $this->mail($dataField['approver']['email'], $approveable);
                    }
                }
            } else {
                $approverList[] = new Approver([
                    'userid' => $approver->userid,
                    'step' => $approver->step,
                ]);
            }
        }

        $approveable->approvers()->saveMany($approverList);

        return $isApprove;
    }

    public function createTask(array $taskData, Model $taskable): void
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

    public function createFile(Request $request, Model $fileable): void
    {
        $uploadedFiles = $request->file('document_files');

        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                $fileData = new File([
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_path' => $file->store('uploads', 'public'),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);

                $fileable->files()->save($fileData);
            }
        }
    }

    private function mail($email, Model $document): bool
    {
        $title = is_string($document->title) ? $document->title : $document->title[0].$document->title[1];
        $detail = $document->detail;
        $detail .= '<br><br><a href="'.route('document.type.approve', ['document_type' => $document->document_tag['document_tag'], 'document_id' => $document->id]).'">Click here to approve</a>';

        if ($email == '' || $email == '-' || config('app.env') == 'local') {
            Mail::create([
                'email' => $email,
                'subject' => $title,
                'body' => $detail,
                'status' => 'Test Send Mail',
            ]);

            return true;
        }

        $mail = Mail::create([
            'email' => $email,
            'subject' => $title,
            'body' => $detail,
        ]);

        $response = Http::withHeaders([
            'content-type' => 'application/json',
            'API_KEY' => config('services.email_api.key'),
        ])->post(config('services.email_api.url'), [
            'To' => [$email],
            'Cc' => [],
            'Bcc' => [],
            'Username' => config('services.email_api.username'),
            'Password' => config('services.email_api.password') ?? 'P@rnchai289',
            'DisplayName' => config('services.email_api.display_name'),
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
