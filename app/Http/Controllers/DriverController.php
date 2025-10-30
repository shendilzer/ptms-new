<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Http\Resources\DriverResource;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Driver/Index');
    }

    public function list(Request $request)
    {
        $query = Driver::query();

        if ($request->has('searchtext') && !empty($request->input('searchtext'))) {
            $search = $request->input('searchtext');
            $query
                ->whereLike('driver_fullname', '%'.$search.'%')
                ->orWhereLike('driver_license_number', '%'.$search.'%');
        }

        if ($request->has('sort_field') && $request->has('sort_direction')) {
            $query->orderBy($request->input('sort_field'), $request->input('sort_direction'));
        } else {
            $query->orderBy('driver_fullname', 'asc'); // Default sorting
        }

        $drivers = DriverResource::collection(
            $query->orderBy('driver_fullname', 'asc')->paginate($request->input('per_page', 10))
        );

        return $drivers;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDriverRequest $request)
    {
        $validatedData = $request->validated();

        $driver = Driver::create($validatedData);

        return response()->json([
            'message' => 'Driver created successfully!',
            'driver' => $driver // Optionally return the created driver data
        ], 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        $driver = Driver::findOrFail($driver->id);

        if (!$driver) {
            return redirect()->back()->with('error', 'Driver not found.');
        }

        return response()->json($driver);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        $validatedData = $request->validated();
        \Log::info('UpdateDriverRequest validated data:', $validatedData);
        $driver->update($validatedData);

        return response()->json([
            'message' => 'Driver updated successfully!',
            'driver' => $driver->fresh() // Return the fresh, updated driver data
        ], 200); // 200 OK status code for successful updates
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $driver = Driver::findOrFail($id); // Find the driver or throw a 404 error
            $driver->delete(); // Delete the driver

            return response()->json(['message' => 'Driver deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete driver.'], 500);
        }
    }
}
