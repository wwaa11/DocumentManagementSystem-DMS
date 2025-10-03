<?php
namespace App\Http\Controllers;

use App\Models\DocumentApprover;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    public function createApprover($data, $approveable)
    {
        $approverList = [];
        if ($data['selfApprove'] == 'true') {
            $approverList[] = [
                'userid'      => auth()->user()->userid,
                'step'        => 1,
                'status'      => 'Approve',
                'approved_at' => date('Y-m-d H:i:s'),
            ];
        } else {
            $approverList[] = [
                'userid' => $data['approver']['userid'],
                'step'   => 1,
            ];
        }

        $approverType = DocumentApprover::where('document_type', $data['document_type'])->get();
        foreach ($approverType as $type) {
            $approverList[] = [
                'userid' => $type->userid,
                'step'   => $type->step + 1,
            ];
        }

        $approveable->approvers()->saveMany($approverList);
    }

    public function createFile(Request $request, $fileable)
    {
        $uploadedFiles = $request->file('document_files');
        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                $originalFilename = $file->getClientOriginalName();
                $mimeType         = $file->getMimeType();
                $size             = $file->getSize();
                $storedPath       = $file->store('public/uploads'); // Store in storage/app/public/uploads

                $fileData = [
                    'original_filename' => $originalFilename,
                    'stored_path'       => $storedPath,
                    'mime_type'         => $mimeType,
                    'size'              => $size,
                ];

                // Create a new File model instance
                $fileEntry = new File($fileData);
                // Associate the file with the fileable model
                $fileable->files()->save($fileEntry);
            }
        }
    }

    public function createLog($log, $logable)
    {
        $logData = [
            'userid' => auth()->user()->userid,
            'action' => $log['action'],
            'detail' => $log['detail'],
        ];

        $logable->logs()->save($logData);
    }
}
