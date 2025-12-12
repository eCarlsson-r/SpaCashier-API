<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\Income;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Sales::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $incomeId = Income::whereYear('date', date("Y"))->orderBy("id", "desc")->first();
        if ($incomeId) $incomeId = $incomeId->id;
        $previousIncomeId = Income::whereYear('date', '<', date("Y"))->orderBy("id", "desc")->first();
        if ($previousIncomeId) $previousIncomeId = $previousIncomeId->id;

        if ($incomeId) {
            $reference = "EXO.BKM.".date("y").sprintf('%05d', ($incomeId-$previousIncomeId)+1);
        } else {
            $reference = "EXO.BKM.".date("y").sprintf('%05d', 1);
        }

        $sales = Sales::create([
            "date" => date("Y-m-d"),
            "time" => date("H:i:s"),
            "branch_id" => $request->branch_id,
            "customer_id" => $request->customer_id,
            "employee_id" => $request->employee_id,
            "subtotal" => $request->subtotal,
            "discount" => $request->discount,
            "rounding" => $request->rounding,
            "total" => $request->total
        ]);

        $sales->records()->createMany($request->records);

        if ($sales) {
            return response()->json($sales, 201);
        } else {
            return response()->json(['message' => 'Failed to create journal'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Sales::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sales $sales)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sales $sales)
    {
        //
    }
}
