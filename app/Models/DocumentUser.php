<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DocumentUser extends Model
{
    protected $table = 'document_users';

    protected $fillable = [
        'requester',
        'document_phone',
        'title',
        'detail',
    ];

    protected $appends = [
        'document_type_name',
        'document_tag',
        'list_detail',
        'status',
    ];

    /**
     * Progress rank for multi-section documents. Lower = earlier / incomplete.
     *
     * @var array<string, int>
     */
    private const STATUS_PROGRESS = [
        'reject' => 0,
        'cancel' => 0,
        'not_approval' => 0,
        'wait_approval' => 1,
        'pending' => 2,
        'process' => 3,
        'done' => 4,
        'complete' => 5,
    ];

    public function getDocumentTypeNameAttribute()
    {
        return 'ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม';
    }

    public function getDocumentTagAttribute()
    {
        return [
            'document_tag' => 'USER',
            'colour' => 'warning',
        ];
    }

    public function getListDetailAttribute()
    {
        return strlen($this->detail) > 100 ? mb_substr($this->detail, 0, 100).'...' : $this->detail;
    }

    public function getStatusAttribute(): ?string
    {
        $statuses = collect($this->getAllDocuments())
            ->pluck('status')
            ->filter()
            ->values();

        if ($statuses->isEmpty()) {
            return null;
        }

        if ($statuses->unique()->count() === 1) {
            return $statuses->first();
        }

        return $this->resolveMixedSectionStatus($statuses);
    }

    /**
     * Overall status follows the slowest incomplete section so a finished PAC
     * section does not hide an unfinished IT section (and vice versa).
     *
     * @param  Collection<int, string>  $statuses
     */
    private function resolveMixedSectionStatus(Collection $statuses): string
    {
        $failureStatuses = ['reject', 'cancel', 'not_approval'];

        foreach ($failureStatuses as $failureStatus) {
            if ($statuses->contains($failureStatus)) {
                return $failureStatus;
            }
        }

        return $statuses
            ->sortBy(fn (string $status): int => self::STATUS_PROGRESS[$status] ?? 0)
            ->first();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'requester', 'userid');
    }

    public function approvers()
    {
        return $this->morphMany(Approver::class, 'approvable');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function getAllDocuments()
    {
        $document_user_id = $this->id;
        $document = [];

        $it = DocumentitUser::where('document_user_id', $document_user_id)->first();
        if ($it) {
            $document[] = $it;
        }
        $pac = DocumentPac::where('document_user_id', $document_user_id)->first();
        if ($pac) {
            $document[] = $pac;
        }
        $hc = DocumentHc::where('document_user_id', $document_user_id)->first();
        if ($hc) {
            $document[] = $hc;
        }
        $heartstream = DocumentHeartstream::where('document_user_id', $document_user_id)->first();
        if ($heartstream) {
            $document[] = $heartstream;
        }
        $registration = DocumentRegister::where('document_user_id', $document_user_id)->first();
        if ($registration) {
            $document[] = $registration;
        }

        return $document;
    }

    public function gettAlllogs()
    {
        $it = DocumentitUser::where('document_user_id', $this->id)->first();

        if ($it === null) {
            return collect();
        }

        return $it->logs->where('action', 'process')->values();
    }
}
