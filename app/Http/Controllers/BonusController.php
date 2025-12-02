<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->input('grade')) {
            return Bonus::with('treatment')->where('grade', $request->input('grade'))->get();
        }
        else return Bonus::with('treatment')->get();
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
    public function show(Bonus $bonus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bonus $bonus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bonus $bonus)
    {
        //
    }
}
