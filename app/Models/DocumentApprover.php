<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentApprover extends Model
{
    //
    protected $table    = 'document_approvers';
    protected $fillable = [
        'document_type',
        'userid',
        'step',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'userid', 'userid');
    }
}
