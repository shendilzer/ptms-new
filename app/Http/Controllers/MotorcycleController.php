<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Motorcycle;
use App\Http\Requests\StoreMotorcycleRequest;
use App\Http\Requests\UpdateMotorcycleRequest;
use App\Http\Resources\MotorcycleResource;

class MotorcycleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Motorcycle/Index');
    }

    public function list(Request $request)
    {
        $query = Motorcycle::query();

        if ($request->has('searchtext') && !empty($request->input('searchtext'))) {
            $search = $request->input('searchtext');
            $query
                ->whereLike('plate_number', '%'.$search.'%')
                ->orWhereLike('make', '%'.$search.'%')
                ->orWhereLike('year_model', '%'.$search.'%');
        }

        if ($request->has('sort_field') && $request->has('sort_direction')) {
            $query->orderBy($request->input('sort_field'), $request->input('sort_direction'));
        } else {
            $query->orderBy('plate_number', 'asc'); // Default sorting
        }

        $motorcycles = MotorcycleResource::collection(
            $query->orderBy('plate_number', 'asc')->paginate($request->input('per_page', 10))
        );

        return $motorcycles;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMotorcycleRequest $request)
    {
        $validatedData = $request->validated();

        $motorcycle = Motorcycle::create($validatedData);

        return response()->json([
            'message' => 'Motorcycle created successfully!',
            'motorcycle' => $motorcycle // Optionally return the created motorcycle data
        ], 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     */
    public function show(Motorcycle $motorcycle)
    {
        $motorcycle = Motorcycle::findOrFail($motorcycle->id);

        if (!$motorcycle) {
            return redirect()->back()->with('error', 'Motorcycle not found.');
        }

        return response()->json($motorcycle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMotorcycleRequest $request, Motorcycle $motorcycle)
    {
        $validatedData = $request->validated();
        \Log::info('UpdateMotorcycleRequest validated data:', $validatedData);
        $motorcycle->update($validatedData);

        return response()->json([
            'message' => 'Motorcycle updated successfully!',
            'motorcycle' => $motorcycle->fresh() // Return the fresh, updated motorcycle data
        ], 200); // 200 OK status code for successful updates
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $motorcycle = Motorcycle::findOrFail($id); // Find the motorcycle or throw a 404 error
            $motorcycle->delete(); // Delete the motorcycle

            return response()->json(['message' => 'Motorcycle deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete motorcycle.'], 500);
        }
    }
}
