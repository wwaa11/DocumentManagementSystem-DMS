<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentIT extends Model
{
    protected $table = 'document_its';

    private $documentStatuses = [
        'support',      // Support Document ขอแจ้งงาน/สนับสนุนการทำงาน
        'user',         // User Document ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม
        'approval',     // Wait for Approval
        'non_approval', // Non-Approval Document
        'cancel',       // Requester cancel the request
        'pending',      // Pending for admin to process
        'reject',       // Document reject by admin
        'process',      // Document is processing
        'done',         // Document is done wait for head of admin to approve
        'finish',       // Document is finished
    ];

}
