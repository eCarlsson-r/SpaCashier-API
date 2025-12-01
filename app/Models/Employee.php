<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    protected $table = 'employees';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'complete_name',
        'name',
        'status',
        'identity_type',
        'identity_number',
        'place_of_birth', 
        'date_of_birth',
        'certified',
        'recruiter',
        'branch_id',
        'base_salary',
        'expertise',
        'gender', 'phone',
        'address', 'mobile', 'email',
        'bank_account',
        'bank'
    ];

    protected $attributes = [
        'absent_deduction' => 50000,
        'meal_fee' => 0,
        'late_deduction' => 20000
    ];

    protected $guarded = [
        'id'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function grade()
    {
        return $this->hasMany(Grade::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public static function getDailyReport($date, $branchId = null)
    {
        $sessionCountSub = self::select('employees.id as employee_id')
            ->selectRaw('COALESCE(COUNT(DISTINCT sessions.customer_id, sessions.date), 0) as session_count')
            ->join('grade', function($join) use ($date) {
                $join->on('employees.id', '=', 'grade.employee_id')
                    ->where('grade.start_date', '<=', $date)
                    ->where(function($query) use ($date) {
                        $query->where('grade.end_date', '>=', $date)
                            ->orWhereNull('grade.end_date');
                    });
            })
            ->join('sessions', 'sessions.employee_id', '=', 'employees.id')
            ->join('bonus', function($join) {
                $join->on('grade.grade', '=', 'bonus.grade')
                    ->on('bonus.treatment_id', '=', 'sessions.treatment_id');
            })
            ->join('treatments', 'sessions.treatment_id', '=', 'treatments.id')
            ->whereBetween('sessions.date', [
                date('Y-m-d', strtotime("last day of previous month", strtotime($date))),
                $date
            ])
            ->groupBy('employees.id');

        $query = self::select(
            'employees.complete_name',
            'employees.name',
            DB::raw("COALESCE(ss.session_count, 0) as session_count"),
            DB::raw("IF(attendance.shift_id='OFF', 'OFF', attendance.clock_in) as clock_in"),
            DB::raw("COUNT(DISTINCT CASE WHEN sessions.status='completed' THEN sessions.customer_id END) as completed_sessions"),
            DB::raw("COUNT(DISTINCT CASE WHEN sessions.status='ongoing' THEN sessions.customer_id END) as ongoing_sessions"),
            DB::raw("
                IF(attendance.shift_id!='OFF' AND attendance.shift_id!='L' AND SUBTIME(attendance.clock_in, shifts.start_time)>0 AND HOUR(SUBTIME(attendance.clock_in, shifts.start_time))>1, 2*employees.late_deduction,
                    IF(attendance.shift_id!='OFF' AND attendance.shift_id!='L' AND SUBTIME(attendance.clock_in, shifts.start_time)>0 AND (HOUR(SUBTIME(attendance.clock_in, shifts.start_time))>0 OR MINUTE(SUBTIME(attendance.clock_in, shifts.start_time))>5), (HOUR(SUBTIME(attendance.clock_in, shifts.start_time))+1)*employees.late_deduction, 0)
                ) +
                IF(((attendance.shift_id='M' OR attendance.shift_id='N') AND HOUR(SUBTIME(attendance.clock_out, shifts.end_time))>=1) OR ((attendance.shift_id='A' OR attendance.shift_id='D') AND HOUR(SUBTIME(attendance.clock_out, shifts.end_time))>1), employees.late_deduction, 0) +
                IF(attendance.shift_id!='OFF' AND attendance.shift_id!='L' AND attendance.clock_in IS NULL AND attendance.date <= DATE_SUB(CURDATE(), INTERVAL 1 DAY), IF(WEEKDAY(attendance.date)>4, employees.absent_deduction*2, employees.absent_deduction), 0)
                as deduction
            ")
        )
        ->join('grade', function($join) use ($date) {
            $join->on('employees.id', '=', 'grade.employee_id')
                ->where('grade.start_date', '<=', $date)
                ->where(function($query) use ($date) {
                    $query->where('grade.end_date', '>=', $date)
                        ->orWhereNull('grade.end_date');
                });
        })
        ->leftJoin('attendance', function($join) use ($date) {
            $join->on('attendance.employee_id', '=', 'employees.id')
                ->where('attendance.date', '=', $date);
        })
        ->leftJoin('shifts', 'attendance.shift_id', '=', 'shifts.id')
        ->leftJoin('sessions', function($join) use ($date) {
            $join->on('employees.id', '=', 'sessions.employee_id')
                ->where('sessions.date', '=', $date);
        })
        ->leftJoinSub($sessionCountSub, 'ss', function($join) {
            $join->on('employees.id', '=', 'ss.employee_id');
        })
        ->where('grade.grade', '<>', 'K')
        ->groupBy('employees.id', 'attendance.id', 'shifts.id', 'ss.session_count')
        ->orderByRaw('-attendance.clock_in DESC');

        if ($branchId) {
            $query->where('employees.branch_id', $branchId);
        }

        return $query->get();
    }
}