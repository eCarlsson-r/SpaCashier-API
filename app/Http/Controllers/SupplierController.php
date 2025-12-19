<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Supplier::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $supplier = Supplier::create($request->all());

        if ($supplier) {
            return response()->json($supplier, 201);
        } else {
            return response()->json(['message' => 'Failed to create supplier'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Supplier::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        if ($supplier->update($request->all())) {
            return response()->json($supplier, 200);
        } else {
            return response()->json(['message' => 'Failed to update supplier'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->delete()) {
            return response()->json(['message' => 'Supplier deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Failed to delete supplier'], 500);
        }
    }
}
