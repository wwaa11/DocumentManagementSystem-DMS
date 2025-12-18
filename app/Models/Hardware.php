<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hardware extends Model
{
    protected $table = 'hardwares';

    protected $fillable = [
        'borrow_id',
        'serial_number',
        'detail',
        'borrow_date',
    ];

    protected $casts = [
        'borrow_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    public function borrow_document()
    {
        return $this->hasOne(DocumentBorrow::class, 'id', 'borrow_id');
    }
}
