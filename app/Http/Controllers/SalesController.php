<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\Branch;
use App\Models\Income;
use App\Models\Journal;
use App\Models\Voucher;
use App\Models\Walkin;
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
        $sales = Sales::find($request->id);
        $incomeId = Income::whereYear('date', date("Y"))->orderBy("id", "desc")->first();
        if ($incomeId) $incomeId = $incomeId->id;
        $previousIncomeId = Income::whereYear('date', '<', date("Y"))->orderBy("id", "desc")->first();
        if ($previousIncomeId) $previousIncomeId = $previousIncomeId->id;

        if ($incomeId) {
            $reference = "EXO.BKM.".date("y").sprintf('%05d', ($incomeId-$previousIncomeId)+1);
        } else {
            $reference = "EXO.BKM.".date("y").sprintf('%05d', 1);
        }

        $income = Income::create([
            "journal_reference" => $reference,
            "date" => date("Y-m-d"),
            "partner_type" => "customer",
            "partner" => $sales->customer_id,
            "description" => "Penjualan No. ".$sales->id." a/n ".$sales->customer->name
        ]);

        $sales->update([
            "income_id" => $income->id
        ]);

        $journal = Journal::create([
            "reference" => $sales->income->journal_reference,
            "date" => date("Y-m-d"),
            "description" => "Penjualan No. ".$sales->id." a/n ".$sales->customer->name
        ]);

        foreach($sales->records as $record) {
            if ($record->redeem_type == "voucher") {
                $start = (int)explode($record->treatment_id, $record->voucher_start)[1];
                $end = (int)explode($record->treatment_id, $record->voucher_end)[1];
                for ($i=$start; $i <= $end; $i++) {
                    $voucherCode = $record->treatment_id.sprintf('%06d', $i);
                    $returnArr = updateDB(
                        $connection, "`voucher`",
                        ["voucher-sales"=>$sales->id, "voucher-customer"=>$sales->customer_id, "voucher-amount"=>$record->price],
                        "`voucher-no`='$voucherCode'"
                    );
                }

                $journal->records()->create([
                    "account_id" => Branch::find($sales->branch_id)->voucher_purchase_account,
                    "debit" => 0,
                    "credit" => $record->total_price,
                    "description" => $record->treatment->name.", No. Voucher: ".$record->voucher_start." - ".$record->voucher_end
                ]);

                $income->items()->create([
                    "type" => "penjualan",
                    "transaction" => $record->id,
                    "amount" => $record->total_price,
                    "description" => "No. Voucher dari ".$record->voucher_start." s/d ".$record->voucher_end
                ]);
            } else if ($record->redeem_type == "walkin") {
                for ($i=0; $i<$record->quantity; $i++) {
                    Walkin::create([
                        "treatment_id" => $record->treatment_id,
                        "customer_id" => $sales->customer_id,
                        "sales_id" => $sales->id
                    ]);
                }

                $journal->records()->create([
                    "account_id" => Branch::find($sales->branch_id)->walkin_account,
                    "debit" => 0,
                    "credit" => $record->total_price,
                    "description" => $record->treatment->name." WALK IN"
                ]);

                $income->items()->create([
                    "type" => "penjualan",
                    "transaction" => $record->id,
                    "amount" => $record->total_price,
                    "description" => "Walk-in ".$record->treatment->name
                ]);
            }
        } 

        if ($request->payment_method == "cash") {
            $income->payments()->create([
                "wallet_id" => Branch::find($sales->branch_id)->cash_account,
                "amount" => $sales->total, "type" => "cash",
                "description" => "Uang Tunai"
            ]);
        } else if ($request->payment_method == "card") {
            $income->payments()->create([
                "wallet_id" => Wallet::where("name", "EDC ".$request->card_edc)->first()->id,
                "amount" => $sales->total, "type" => "card",
                "description" => "Kartu ".$request->card_type." dengan nomor ".$request->card_number
            ]);
        } else if ($request->payment_method == "ewallet") {
            $income->payments()->create([
                "wallet_id" => Wallet::where("name", "EDC ".$request->wallet_edc)->first()->id,
                "amount" => $sales->total, "type" => "card",
                "description" => "E-Wallet ".$request->wallet_edc." dengan nomor ".$request->mobile_number
            ]);
        } else if ($request->payment_method == "voucher") {
            $income->payments()->create([
                "wallet_id" => Wallet::where("name", "Voucher ".$request->voucher_provider)->first()->id,
                "amount" => $sales->total, "type" => "card",
                "description" => "Voucher ".$request->voucher_provider." dengan nomor ".$request->voucher_number
            ]);
        } else if ($request->payment_method == "qr") {
            $income->payments()->create([
                "wallet_id" => Wallet::where("name", "AIO : Kode QR ".$request->qr_edc)->first()->id,
                "amount" => $sales->total, "type" => "card",
                "description" => "Kode QR ".$request->qr_edc
            ]);
        }

        if ($sales) {
            return response()->json($sales, 200);
        } else {
            return response()->json(['message' => 'Failed to update sales'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sales $sales)
    {
        //
    }
}
