<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operator;
use App\Http\Requests\StoreOperatorRequest;
use App\Http\Requests\UpdateOperatorRequest;
use App\Http\Resources\OperatorResource;
use App\Models\Driver;
use App\Models\Motorcycle;
use App\Models\Toda;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = Driver::orderBy('driver_fullname','asc')->get(['id', 'driver_fullname']);
        $motorcycles = Motorcycle::orderBy('plate_number','asc')->get(['id', 'plate_number']);
        $toda = Toda::orderBy('toda_name','asc')->get(['id', 'toda_name']);

        return inertia('Operator/Index', [
            'drivers' => $drivers,
            'motorcycles' => $motorcycles,
            'toda' => $toda,
        ]);
    }

    public function list(Request $request)
    {
        $query = Operator::query()->with(['driver', 'motorcycle', 'toda']);

        if ($request->has('searchtext') && !empty($request->input('searchtext'))) {
            $search = $request->input('searchtext');
            $query
                ->whereLike('fullname', '%'.$search.'%')
                ->orWhereLike('mtop_number', '%'.$search.'%')
                ->orWhereLike('email_address', '%'.$search.'%')
                ->orWhereHas('driver', function($q) use ($search) {
                    $q->whereLike('driver_fullname', '%'.$search.'%');
                })
                ->orWhereHas('motorcycle', function($q) use ($search) {
                    $q->whereLike('plate_number', '%'.$search.'%');
                })
                ->orWhereHas('toda', function($q) use ($search) {
                    $q->whereLike('toda_name', '%'.$search.'%');
                });
        }

        if ($request->has('sort_field') && $request->has('sort_direction')) {
            $query->orderBy($request->input('sort_field'), $request->input('sort_direction'));
        } else {
            $query->orderBy('fullname', 'asc'); // Default sorting
        }

        $operators = OperatorResource::collection(
            $query->orderBy('fullname', 'asc')->paginate($request->input('per_page', 10))
        );

        return $operators;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOperatorRequest $request)
    {
        $validatedData = $request->validated();

        $operator = Operator::create($validatedData);

        return response()->json([
            'message' => 'Operator created successfully!',
            'operator' => $operator // Optionally return the created operator data
        ], 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     */
    public function show(Operator $operator)
    {
        $operator = Operator::with(['driver', 'motorcycle', 'toda'])->findOrFail($operator->id);

        if (!$operator) {
            return redirect()->back()->with('error', 'Operator not found.');
        }

        return response()->json($operator);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperatorRequest $request, Operator $operator)
    {
        $validatedData = $request->validated();
        \Log::info('UpdateOperatorRequest validated data:', $validatedData);
        $operator->update($validatedData);

        return response()->json([
            'message' => 'Operator updated successfully!',
            'operator' => $operator->fresh() // Return the fresh, updated operator data
        ], 200); // 200 OK status code for successful updates
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $operator = Operator::findOrFail($id); // Find the operator or throw a 404 error
            $operator->delete(); // Delete the operator

            return response()->json(['message' => 'Operator deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete operator.'], 500);
        }
    }
}
