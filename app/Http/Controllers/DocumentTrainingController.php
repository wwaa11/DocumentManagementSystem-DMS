<?php
namespace App\Http\Controllers;

use App\Models\DocumentTraining;
use App\Models\DocumentTrainingMentor;
use App\Models\DocumentTrainingParticipant;
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
            'start_date'          => 'required|date',
            'end_date'            => 'required|date',
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i',
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
        $document->start_date = $request->start_date;
        $document->end_date   = $request->end_date;
        $document->start_time = $request->start_time;
        $document->end_time   = $request->end_time;
        $document->hours      = $request->duration_hours;
        $document->minutes    = $request->duration_minutes ?? 0;
        $document->detail     = $detail;
        $document->save();

        $approverField = [
            'selfApprove' => false,
            'approver'    => $approver,
        ];
        $taskData = [
            'document_type' => 'training',
            'selfApprove'   => false,
            'approver'      => $request['approver'],
        ];
        $this->helper->createApprover('training', $approverField, $document);
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

        return redirect()->route('document.index')->with('success', 'สร้างเอกสารสำเร็จ!');
    }

    public function createProject(Request $request)
    {
        $projectId = $request->project_id;

        $project = DocumentTraining::find($projectId);
        if (! $project) {
            return redirect()->route('document.index')->with('error', 'โปรเจกต์ไม่พบ!');
        }

        $participants = $project->participants()->pluck('participant')->toArray();

        $postData = [
            "type"       => "multiple",
            "title"      => $project->title,
            "detail"     => $project->detail,
            "start_date" => $project->start_date->format('Y-m-d'),
            "end_date"   => $project->end_date->format('Y-m-d'),
            "start_time" => $project->start_time->format('H:i'),
            "end_time"   => $project->end_time->format('H:i'),
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
                'task_name'     => 'เสร็จสิ้นการฝึกอบรมเสร็จสิ้น',
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

        return response()->json($response, 200);
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

        $project->status = 'complete';
        $project->save();

        $project->tasks()->where('step', 3)->update([
            'status'        => 'approve',
            'task_name'     => 'เสร็จสิ้นการฝึกอบรมเสร็จสิ้น',
            'task_user'     => auth()->user()->userid,
            'task_position' => auth()->user()->position,
            'date'          => date('Y-m-d H:i:s'),
        ]);

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
}
