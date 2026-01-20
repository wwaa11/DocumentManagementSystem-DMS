<?php
namespace App\Http\Controllers;

use App\Models\DocumentTraining;
use App\Models\DocumentTrainingMentor;
use App\Models\DocumentTrainingParticipant;
use Illuminate\Http\Request;

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
}
