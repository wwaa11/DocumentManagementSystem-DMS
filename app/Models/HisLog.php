<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisLog extends Model
{
    protected $table = 'his_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reported_at',
        'reporter',
        'module',
        'problem_detail',
        'receiver',
        'receiver_userid',
        'fixer',
        'root_cause',
        'status',
        'time',
        'shift',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_at' => 'date',
        ];
    }

    /**
     * @return list<string>
     */
    public static function moduleOptions(): array
    {
        return [
            'OPD',
            'IPD',
            'Billing',
            'Lab',
            'Pharmacy',
            'Appointment',
            'OR',
            'EMR',
            'Interface',
            'Xray',
            'Consent form',
            'Assessment',
            'Package',
            'CPOE',
            'eMar',
            'Log in',
            'VNA',
            'Nutrition',
            'Report',
            'Scan',
            'Admit',
            'Patient Info',
            'Other',
        ];
    }

    /**
     * @return list<string>
     */
    public static function fixerRoles(): array
    {
        return [
            'it',
            'it-approve',
            'it-hardware',
            'it-hardware-approve',
            'admin',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    public static function fixerUsers()
    {
        return User::query()
            ->whereIn('role', self::fixerRoles())
            ->orderBy('department')
            ->orderBy('name')
            ->get(['userid', 'name', 'role', 'department']);
    }

    /**
     * @return list<string>
     */
    public static function fixerOptions(): array
    {
        return self::fixerUsers()
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function statusOptions(): array
    {
        return [
            'Open',
            'In Progress',
            'Closed',
        ];
    }

    /**
     * @return list<string>
     */
    public static function shiftOptions(): array
    {
        return [
            'เช้า',
            'บ่าย',
            'ดึก',
        ];
    }

    public static function resolveShiftFromTime(string $time): string
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $totalMinutes = ($hour * 60) + $minute;

        if ($totalMinutes >= (7 * 60) && $totalMinutes <= (12 * 60)) {
            return 'เช้า';
        }

        if ($totalMinutes >= ((12 * 60) + 1) && $totalMinutes < (17 * 60)) {
            return 'บ่าย';
        }

        return 'ดึก';
    }
}
