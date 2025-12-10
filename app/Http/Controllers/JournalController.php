<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Journal::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $journal = Journal::create($request->all());

        $journal->records()->createMany($request->records);

        if ($journal) {
            return response()->json($journal, 201);
        } else {
            return response()->json(['message' => 'Failed to create journal'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Journal $journal)
    {
        return $journal;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Journal $journal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Journal $journal)
    {
        if ($journal->delete()) {
            return response()->json(['message' => 'Journal deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Failed to delete journal'], 500);
        }
    }
}
