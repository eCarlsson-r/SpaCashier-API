<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Transfer::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $transferId = Transfer::whereYear('date', date("Y"))->orderBy("id", "desc")->first();
        if ($transferId) $transferId = $transferId->id;
        $previousTransferId = Transfer::whereYear('date', '<', date("Y"))->orderBy("id", "desc")->first();
        if ($previousTransferId) $previousTransferId = $previousTransferId->id;

        if ($transferId) {
            $reference = "EXO.BPB.".date("y").sprintf('%05d', ($transferId-$previousTransferId)+1);
        } else {
            $reference = "EXO.BPB.".date("y").sprintf('%05d', 1);
        }
        $transfer = Transfer::create([
            'journal_reference' => $reference,
            'date' => $request->date,
            'from_wallet_id' => $request->from_wallet_id,
            'to_wallet_id' => $request->to_wallet_id,
            'amount' => $request->amount,
            'description' => $request->description
        ]);
        
        if ($transfer) {
            return response()->json($transfer, 201);
        } else {
            return response()->json(['message' => 'Failed to create transfer'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transfer $transfer)
    {
        return $transfer;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transfer $transfer)
    {
        if ($transfer->update($request->all())) {
            return response()->json($transfer, 200);
        } else {
            return response()->json(['message' => 'Failed to update transfer'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transfer $transfer)
    {
        //
    }
}
