<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (isset($request->period)) {
            $period = str_replace("-W", " ", $request->period);

            $attendance = Attendance::with('employee')
            ->whereBetween('date', ["STR_TO_DATE('".$period." Monday', '%x %v %W')", "STR_TO_DATE('".$period." Sunday', '%x %v %W')"])
            ->get();

            if (!($attendance->isEmpty())) {
                $employee = '';
                $employeeSchedule = collect();
                foreach($attendance as $data) {
                    $job_type = ($data->employee->grade->grade == 'K') ? 'cashier' : 'therapist';
                    if ($employee == $data->employee_id) {
                        $employeeSchedule[$data->employee_id."-".$data->employee->complete_name."-".$job_type]->push($data);
                    } else {
                        $employeeSchedule[$data->employee_id."-".$data->employee->complete_name."-".$job_type] = collect($data);
                        $employee = $data->employee_id;
                    }
                }

                $schedule = collect();
                foreach($employeeSchedule as $idx => $data) {
                    $employee = explode("-", $idx);
                    $empSchedule = array("id"=>$employee[0], "name"=>$employee[1], "job_type"=>$employee[2]);

                    foreach($data as $day) {
                        $empSchedule[Carbon::parse($day->date)->format('l')] = $day->shift_id;
                    }

                    $schedule->push($empSchedule);
                }

                return response()->json($schedule);
            } else {
                return response()->json(['message' => 'No attendance found'], 404);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
