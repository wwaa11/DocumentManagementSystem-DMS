<?php
namespace App\Http\Controllers;

use App\Models\DocumentTraining;
use App\Models\DocumentTrainingMentor;
use App\Models\DocumentTrainingParticipant;
use App\Models\DocumentTrainingDate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DocumentTrainingController extends Controller
{
    private $helper;

    public function __construct()
    {
        $this->helper = new HelperController();
    }

    public function createDocument(Request $request)
    {
        $request->validate([
            'approver'            => 'required|array',
            'mentors_userid'      => 'nullable|array',
            'participants_userid' => 'required|array',
            'training_name'       => 'required|string',
            'date_mode'           => 'required|string|in:range,specific',
            'start_date'          => 'required_if:date_mode,range|nullable|date',
            'end_date'            => 'required_if:date_mode,range|nullable|date',
            'start_time'          => 'required_if:date_mode,range|nullable',
            'end_time'            => 'required_if:date_mode,range|nullable',
            'specific_date'       => 'required_if:date_mode,specific|array',
            'specific_start_time' => 'required_if:date_mode,specific|array',
            'specific_end_time'   => 'required_if:date_mode,specific|array',
            'duration_hours'      => 'required|integer',
            'duration_minutes'    => 'nullable|integer',
            'source_type'         => 'required|string',
        ]);

        $approver = $request['approver'];
        $detail   = '';
        switch ($request->source_type) {
            case 'in_plan':
                $detail .= 'จัดในแผน ลำดับที่ ' . $request->plan_no . '(อ้างอิงลำดับที่ในแผนการฝึกอบรมประจำปี)';
                break;
            case 'substitute':
                $detail .= 'จัดแทนในแผน เรื่อง ' . $request->substitute_topic . ' เนื่องจาก ' . $request->substitute_reason;
                break;
            case 'out_of_plan':
                $detail .= 'จัดนอกแผน เนื่องจาก ' . $request->out_of_plan_reason;
                break;
        }

        $document             = new DocumentTraining();
        $document->requester  = auth()->user()->userid;
        $document->title      = $request->training_name;
        $document->hours      = $request->duration_hours;
        $document->minutes    = $request->duration_minutes ?? 0;
        $document->detail     = $detail;
        $document->save();

        // Create Dates
        if ($request->date_mode === 'range') {
            $current = new \DateTime($request->start_date);
            $end = new \DateTime($request->end_date);
            while ($current <= $end) {
                DocumentTrainingDate::create([
                    'document_training_id' => $document->id,
                    'date'                 => $current->format('Y-m-d'),
                    'start_time'           => $request->start_time,
                    'end_time'             => $request->end_time,
                ]);
                $current->modify('+1 day');
            }
        } else {
            foreach ($request->specific_date as $index => $date) {
                if ($date) {
                    DocumentTrainingDate::create([
                        'document_training_id' => $document->id,
                        'date'                 => $date,
                        'start_time'           => $request->specific_start_time[$index],
                        'end_time'             => $request->specific_end_time[$index],
                    ]);
                }
            }
        }

        $approverField = [
            'selfApprove' => false,
            'approver'    => $approver,
        ];
        $taskData = [
            'document_type' => 'training',
            'selfApprove'   => false,
            'approver'      => $request['approver'],
        ];
        $isApprove = $this->helper->createApprover('training', $approverField, $document);
        $this->helper->createFile($request, $document);
        $this->helper->createTask($taskData, $document);

        $mentors = $request->mentors_userid ?? [];
        foreach ($mentors as $index => $mentor) {
            $documentTrainingMentor                       = new DocumentTrainingMentor();
            $documentTrainingMentor->document_training_id = $document->id;
            $documentTrainingMentor->mentor               = $mentor;
            $documentTrainingMentor->mentor_name          = $request->mentors_name[$index];
            $documentTrainingMentor->mentor_position      = $request->mentors_position[$index];
            $documentTrainingMentor->save();
        }

        $participants = $request->participants_userid;
        foreach ($participants as $index => $participant) {
            $documentTrainingParticipant                         = new DocumentTrainingParticipant();
            $documentTrainingParticipant->document_training_id   = $document->id;
            $documentTrainingParticipant->participant            = $participant;
            $documentTrainingParticipant->participant_name       = $request->participants_name[$index];
            $documentTrainingParticipant->participant_position   = $request->participants_position[$index];
            $documentTrainingParticipant->participant_department = $request->participants_dept[$index];
            $documentTrainingParticipant->save();
        }

        if($isApprove){
            $this->createProject($document->id);
        }

        return redirect()->route('document.index')->with('success', 'สร้างเอกสารสำเร็จ!');
    }

    public function createProject($projectId)
    {
        $project = DocumentTraining::find($projectId);
        $participants = $project->participants()->pluck('participant')->toArray();
        $dates = $project->dates()->get();
        
        $firstDate = $dates->first();
        $lastDate = $dates->last();

        $startRegister = $firstDate->dateString . ' ' . $firstDate->start_time;
        $endRegister = $lastDate->dateString . ' ' . $lastDate->end_time;

        $postData = [
            "document_id" => $project->id,
            "type"       => "multiple",
            "title"      => $project->title,
            "detail"     => $project->detail,
            "project_start_register" => $startRegister,
            "project_end_register" => $endRegister,
            "dates"      => $dates->map(function($d) {
                return [
                    'dateString' => $d->dateString,
                    'start_time' => $d->start_time,
                    'end_time'   => $d->end_time,
                ];
            })->toArray(),
            "users"      => $participants,
        ];

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . env('API_TRAINING'),
        ])
            ->withoutVerifying()
            ->post('https://pr9web.praram9.com/w_hrd/api/create-project', $postData);

        if ($response->successful()) {
            $res                  = $response->json();
            $project->training_id = $res['project']['id'];
            $project->save();

            $project->tasks()->where('step', 2)->update([
                'status'        => 'approve',
                'task_name'     => 'ระหว่างการฝึกอบรม',
                'task_user'     => auth()->user()->userid,
                'task_position' => auth()->user()->position,
                'date'          => date('Y-m-d H:i:s'),
            ]);

            $project->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'create_project',
                'details' => 'สร้างโครงการฝึกอบรม ' . $project->title . ' สำเร็จ!',
            ]);

            $response = [
                'status'  => 'success',
                'message' => 'สร้างโปรเจกต์สำเร็จ!',
            ];
        } else {
            $response = [
                'status'  => 'failed',
                'message' => 'สร้างโปรเจกต์ไม่สำเร็จ!',
            ];
        }

        return $response;
    }

    public function getAttendance(Request $request)
    {
        $projectId = $request->project_id;
        $project   = DocumentTraining::find($projectId);

        if (! $project) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . env('API_TRAINING'),
        ])
            ->withoutVerifying()
            ->get('https://pr9web.praram9.com/w_hrd/api/get-transaction?project_id=' . $project->training_id);

        if ($response->successful()) {
            $response = $response->json();

        } else {
            $response = [
                'status'  => 'failed',
                'message' => 'ดึงข้อมูลการไม่สำเร็จ!',
            ];
        }

        return response()->json($response, 200);
    }

    public function approveAttendance(Request $request)
    {
        $id        = $request->id;
        $projectId = $request->project_id;
        $project   = DocumentTraining::find($projectId);

        if (! $project) {

            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . env('API_TRAINING'),
        ])
            ->withoutVerifying()
            ->post('https://pr9web.praram9.com/w_hrd/api/approve-transaction', ['transaction_id' => $id]);

        if ($response->successful()) {
            $response = $response->json();

            $project->logs()->create([
                'userid'  => auth()->user()->userid,
                'action'  => 'approve_attendance',
                'details' => 'อนุมัติการเข้าร่วม ' . $request->userid . ' สำเร็จ!',
            ]);
        } else {
            $response = [
                'status'  => 'failed',
                'message' => 'อนุมัติการเข้าร่วมไม่สำเร็จ!',
            ];
        }

        return response()->json($response, 200);
    }

    public function closeProject(Request $request)
    {
        $projectId = $request->project_id;
        $project   = DocumentTraining::find($projectId);

        if (! $project) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        $project->status = 'done';
        $project->save();
        
        // Change to waiting for HR form Training Web to stamp to close project
        // $project->status = 'complete';
        // $project->tasks()->where('step', 3)->update([
        //     'status'        => 'approve',
        //     'task_name'     => 'เสร็จสิ้นการฝึกอบรม',
        //     'task_user'     => auth()->user()->userid,
        //     'task_position' => auth()->user()->position,
        //     'date'          => date('Y-m-d H:i:s'),
        // ]);

        $project->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'close_project',
            'details' => 'ปิดโครงการฝึกอบรม ' . $project->title . ' สำเร็จ!',
        ]);

        $response = [
            'status'  => 'success',
            'message' => 'ปิดโครงการฝึกอบรมสำเร็จ!',
        ];

        return response()->json($response, 200);
    }

    public function saveAssessment(Request $request)
    {
        $projectId = $request->project_id;
        $assessments = $request->assessments; // Array of [participant_id => [date, type, score]]

        foreach ($assessments as $participantId => $data) {
            DocumentTrainingParticipant::where('id', $participantId)
                ->where('document_training_id', $projectId)
                ->update([
                    'assetment_date' => $data['date'],
                    'assetment_type' => $data['type'],
                    'score'          => $data['score'],
                ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'บันทึกข้อมูลการประเมินสำเร็จ!',
        ]);
    }

    public function downloadPDF($id)
    {
        $project = DocumentTraining::with(['participants', 'mentors', 'creator'])->find($id);
        $project_owner = $project->tasks()->where('step', 3)->first()->user;
        $project_date = $project->tasks()->where('step', 3)->first()->date;

        if (!$project) {
            return redirect()->back()->with('error', 'โปรเจกต์ไม่พบ!');
        }

        if ($project->status !== 'complete') {
            return response()->json(['message' => 'Project is not closed.'], 403);
        }

        $pdf = Pdf::loadView('document.training.pdf', compact('project', 'project_owner', 'project_date'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download("Training_{$project->id}.pdf");
    }

    public function cancelProject(Request $request)
    {
        $projectId = $request->project_id;
        $project   = DocumentTraining::find($projectId);

        if (! $project) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }
        $project->status = 'cancel';
        $project->save();

        $project->logs()->create([
            'userid'  => auth()->user()->userid,
            'action'  => 'cancel_project',
            'details' => 'ยกเลิกโครงการฝึกอบรม ' . $project->title . ' สำเร็จ!',
        ]);

        if($project->training_id != null){
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . env('API_TRAINING'),
            ])
                ->withoutVerifying()
                ->post('https://pr9web.praram9.com/w_hrd/api/cancel-project', ['project_id' => $project->training_id]);
        }

        $response = [
            'status'  => 'success',
            'message' => 'ยกเลิกโครงการฝึกอบรมสำเร็จ!',
        ];

        return response()->json($response, 200);
    }
}
