<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hardware extends Model
{
    protected $table = 'hardwares';

    protected $fillable = [
        'borrow_id',
        'serial_number',
        'borrow_date',
    ];

    public function borrow_document()
    {
        return $this->hasOne(DocumentBorrow::class, 'borrow_id', 'id');
    }
}
