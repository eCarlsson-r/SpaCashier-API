<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Period::all();
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
    public function show(Period $period)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $period = Period::create([
            "id" => $request->id,
            "start" => $request->start,
            "end" => $request->end
        ]);
        
        if ($period) {
            return response()->json($period, 201);
        } else {
            return response()->json(['message' => 'Failed to create period'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Period $period)
    {
        if ($period->delete()) {
            return response()->json(['message' => 'Period deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Failed to delete period'], 500);
        }
    }
}
