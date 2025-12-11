<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_belongs_to_employee()
    {
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create(['employee_id' => $employee->id]);

        $this->assertInstanceOf(Employee::class, $attendance->employee);
        $this->assertTrue($attendance->employee->is($employee));
    }

    public function test_attendance_belongs_to_shift()
    {
        $shift = Shift::factory()->create(['id' => 'M']);
        $attendance = Attendance::factory()->create(['shift_id' => $shift->id]);

        $this->assertInstanceOf(Shift::class, $attendance->shift);
        $this->assertTrue($attendance->shift->is($shift));
    }

    public function test_deduction_attribute_returns_zero_for_off_shift()
    {
        $shift = Shift::factory()->create(['id' => 'OFF']);
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => 'OFF',
        ]);

        $this->assertEquals(0, $attendance->deduction);
    }

    public function test_deduction_attribute_returns_zero_for_on_time_arrival()
    {
        $shift = Shift::factory()->create([
            'id' => 'M',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => 'M',
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '08:55:00', // arrived early
            'clock_out' => '17:00:00',
        ]);

        $this->assertEquals(0, $attendance->deduction);
    }

    public function test_deduction_attribute_calculates_late_penalty()
    {
        $shift = Shift::factory()->create([
            'id' => 'M',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
        $employee = Employee::factory()->create([
            'late_deduction' => 20000,
        ]);
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => 'M',
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '09:30:00', // 30 minutes late
            'clock_out' => '17:00:00',
        ]);

        // 30 minutes late = 1 * late_deduction = 20000
        $this->assertEquals(20000, $attendance->deduction);
    }
}
