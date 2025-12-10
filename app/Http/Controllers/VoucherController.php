<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->input("treatment")) {
            return Voucher::where("treatment_id", $request->input("treatment"))->where("id", "LIKE", $request->input("treatment")."%")->orderBy("id", "desc")->first();
        } else return Voucher::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $treatment = $request->input("treatment");
        $voucherStart = $request->input("start");
        $start = (int)explode($treatment, $voucherStart)[1];
        $voucherEnd = $request->input("end");
        $end = (int)explode($treatment, $voucherEnd)[1];
        $date = date("Y-m-d");
        $time = date("H:i:s");
        $connection = connectToDB();
        $treatmentInfo = Treatment::where("id", $treatment)->first();
        $existingVouchers = Voucher::whereIn("id", [$voucherStart, $voucherEnd])->get();
        
        if ($existingVouchers->count() > 0) {
            return response()->json($existingVouchers, 200);
        } else {
            $vouchers = array();
            for ($i=$start; $i <= $end; $i++) {
                $voucherCode = $treatment.sprintf('%06d', $i);
                array_push($vouchers, [
                    "id" => $voucherCode,
                    "treatment_id" => $treatment,
                    "register_date" => $date,
                    "register_time" => $time,
                ]);
            }
            $voucher = Voucher::createMany($vouchers);
            return response()->json($voucher, 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        if ($request->quantity) {
            $quantity = $request->quantity;
            $voucherEnd = substr($id, 0, 4).sprintf('%06d', intval(substr($id, 4))+(intval($quantity)-1));
            return Voucher::select(
                'vouchers.*', 
                'sales.income_id', 'sales.date AS sales_date', 
                'sessions.id', 'sessions.date AS session_date',
                'employees.name'
            )->leftJoin('sales', 'sales.id', '=', 'vouchers.sales_id')
            ->leftJoin('sessions', 'sessions.id', '=', 'vouchers.session_id')
            ->leftJoin('employees', 'employees.id', '=', 'sessions.employee_id')
            ->where("vouchers.id BETWEEN " . $id . " AND " . $voucherEnd)->get();
        } else {
            return Voucher::select(
                'voucher.amount', 'voucher.customer_id', 'voucher.id', 'sessions.date AS session_date', 
                'voucher.purchase_date', 'voucher.register_date', 'voucher.register_time', 'employees.name',
                'voucher.sales_id', 'incomes.journal_reference', 'voucher.session_id', 'voucher.treatment_id'
            )->leftJoin('sales', 'sales.id', '=', 'voucher.sales_id')->leftJoin('sessions', 'sessions.id', '=', 'voucher.session_id')
            ->leftJoin('incomes', 'incomes.id', '=', 'sales.income_id')->leftJoin('employees', 'employees.id', '=', 'sessions.employee_id')
            ->where('voucher.id', $id)->first();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        //
    }
}