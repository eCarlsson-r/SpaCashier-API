<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Treatment;
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
        $treatment = $request->input("treatment_id");
        $voucherStart = $request->input("start");
        $start = (int)explode($treatment, $voucherStart)[1];
        $voucherEnd = $request->input("end");
        $end = (int)explode($treatment, $voucherEnd)[1];
        $date = date("Y-m-d");
        $time = date("H:i:s");
        $treatmentInfo = Treatment::where("id", $treatment);
        $existingVouchers = Voucher::whereBetween("id", [$voucherStart, $voucherEnd])->get();
        
        if ($treatmentInfo->first()) {
            if ($existingVouchers->count() > 0) {
                return response()->json($existingVouchers, 200);
            } else {
                $voucher = collect();
                for ($i=$start; $i <= $end; $i++) {
                    $voucherCode = $treatment.sprintf('%06d', $i);
                    $voucher = Voucher::create([
                        "id" => $voucherCode,
                        "treatment_id" => $treatmentInfo->first()->id,
                        "register_date" => $date,
                        "register_time" => $time,
                    ]);
                    $voucher->push($voucher);
                }
                return response()->json($voucher, 201);
            }
        } else {
            return response()->json([
                "message" => "Treatment not found"
            ], 500);
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
                'voucher.*', 
                'sales.income_id', 'sales.date AS sales_date', 
                'sessions.id', 'sessions.date AS session_date',
                'employees.name'
            )->leftJoin('sales', 'sales.id', '=', 'voucher.sales_id')
            ->leftJoin('sessions', 'sessions.id', '=', 'voucher.session_id')
            ->leftJoin('employees', 'employees.id', '=', 'sessions.employee_id')
            ->whereBetween("voucher.id", [$id, $voucherEnd])->get();
        } else {
            $voucher = Voucher::leftJoin('sales', 'sales.id', '=', 'voucher.sales_id')
                ->leftJoin('sessions', 'sessions.id', '=', 'voucher.session_id')
                ->leftJoin('incomes', 'incomes.id', '=', 'sales.income_id')
                ->leftJoin('employees', 'employees.id', '=', 'sessions.employee_id')
                ->select(
                    'voucher.*', 
                    'sessions.date AS session_date', 
                    'incomes.journal_reference', 
                    'employees.name AS therapist_name'
                )
                ->findOrFail($id);

            if (!$voucher) return response()->json(['message' => 'Not found'], 404);

            return [
                'amount'        => $voucher->amount,
                'id'            => $voucher->id,
                'customer_id'   => $voucher->customer_id,
                'treatment_id'  => $voucher->treatment_id,
                'register_date' => $voucher->register_date,
                'sales_info'    => ($voucher->sales_id > 0) 
                    ? "Date : " . date('d-m-Y', strtotime($voucher->purchase_date)) . "\n" .
                    "Income Reference : " . $voucher->journal_reference
                    : "-------",
                'usage_info'    => ($voucher->session_id > 0)
                    ? "ID : " . $voucher->session_id . "\n" .
                    "Date : " . date('d-m-Y', strtotime($voucher->session_date)) . "\n" .
                    "Therapist : " . $voucher->therapist_name
                    : "-------",
            ];
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