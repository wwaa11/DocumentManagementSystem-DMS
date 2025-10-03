<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentListApprover extends Model
{
    public $timestamps = false;

    protected $table = 'document_list_approvers';

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
