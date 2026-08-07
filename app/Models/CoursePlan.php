<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CoursePlan extends Model
{
    /** @use HasFactory<\Database\Factories\CoursePlanFactory> */
    use HasFactory;

    protected $fillable = [
        'year',
        'department',
        'created_by',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function trainingTypeLabels(): array
    {
        return CoursePlanItem::trainingTypeLabels();
    }

    /**
     * @return array<int, string>
     */
    public static function monthLabels(): array
    {
        return CoursePlanItem::monthLabels();
    }

    /**
     * @return array{document_tag: string, colour: string}
     */
    public function getDocumentTagAttribute(): array
    {
        return [
            'document_tag' => 'COURSE',
            'colour' => 'accent',
        ];
    }

    public function getDocumentNumberAttribute(): string
    {
        return 'COURSE-'.$this->year.'-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getDocumentTypeNameAttribute(): string
    {
        return 'แผนหลักสูตรการฝึกอบรมประจำปี';
    }

    public function getTitleAttribute(): string
    {
        return 'ปี '.$this->year.' · '.$this->department;
    }

    public function getDetailAttribute(): string
    {
        $count = $this->relationLoaded('items')
            ? $this->items->count()
            : $this->items()->count();

        return 'จำนวน '.$count.' หลักสูตร';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'userid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CoursePlanItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function approvers(): HasMany
    {
        return $this->hasMany(CourseApprover::class)->orderBy('level');
    }

    public function resetApprovals(): void
    {
        $this->approvers()->update([
            'status' => 'wait',
            'approved_at' => null,
        ]);

        $this->update(['status' => 'wait_approval']);
    }

    /**
     * @return Collection<int, CourseApprover>
     */
    private function orderedApprovers(): Collection
    {
        return $this->approvers->sortBy('level')->values();
    }

    /**
     * The approver whose turn it currently is, or null when nothing is pending.
     */
    public function currentApprover(): ?CourseApprover
    {
        $approvers = $this->orderedApprovers();
        $next = $approvers->firstWhere('status', 'wait');

        if (! $next) {
            return null;
        }

        $blocked = $approvers
            ->filter(fn (CourseApprover $approver): bool => $approver->level < $next->level)
            ->contains(fn (CourseApprover $approver): bool => $approver->status !== 'approve');

        return $blocked ? null : $next;
    }

    /**
     * Consecutive pending levels held by the same approver, stamped together on approval.
     *
     * @return Collection<int, CourseApprover>
     */
    public function pendingLevelsFor(string $userid): Collection
    {
        $current = $this->currentApprover();

        if (! $current || $current->userid !== $userid) {
            return collect();
        }

        return $this->orderedApprovers()
            ->filter(fn (CourseApprover $approver): bool => $approver->level >= $current->level)
            ->values()
            ->takeWhile(fn (CourseApprover $approver): bool => $approver->userid === $userid)
            ->values();
    }
}
