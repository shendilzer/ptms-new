<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Toda;
use App\Http\Requests\StoreTodaRequest;
use App\Http\Requests\UpdateTodaRequest;
use App\Http\Resources\TodaResource;

class TodaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Toda/Index');
    }

    public function list(Request $request)
    {
        $query = Toda::query();

        if ($request->has('searchtext') && !empty($request->input('searchtext'))) {
            $search = $request->input('searchtext');
            $query
                ->whereLike('toda_name', '%'.$search.'%')
                ->orWhereLike('toda_president', '%'.$search.'%');
        }

        if ($request->has('sort_field') && $request->has('sort_direction')) {
            $query->orderBy($request->input('sort_field'), $request->input('sort_direction'));
        } else {
            $query->orderBy('toda_name', 'asc'); // Default sorting
        }

        $toda = TodaResource::collection(
            $query->orderBy('toda_name', 'asc')->paginate($request->input('per_page', 10))
        );

        return $toda;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTodaRequest $request)
    {
        $validatedData = $request->validated();

        $toda = Toda::create($validatedData);

        return response()->json([
            'message' => 'TODA created successfully!',
            'toda' => $toda // Optionally return the created toda data
        ], 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     */
    public function show(Toda $toda)
    {
        $toda = Toda::findOrFail($toda->id);

        if (!$toda) {
            return redirect()->back()->with('error', 'TODA not found.');
        }

        return response()->json($toda);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTodaRequest $request, Toda $toda)
    {
        $validatedData = $request->validated();
        \Log::info('UpdateTodaRequest validated data:', $validatedData);
        $toda->update($validatedData);

        return response()->json([
            'message' => 'TODA updated successfully!',
            'toda' => $toda->fresh() // Return the fresh, updated toda data
        ], 200); // 200 OK status code for successful updates
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $toda = Toda::findOrFail($id); // Find the toda or throw a 404 error
            $toda->delete(); // Delete the toda

            return response()->json(['message' => 'TODA deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete TODA.'], 500);
        }
    }
}
