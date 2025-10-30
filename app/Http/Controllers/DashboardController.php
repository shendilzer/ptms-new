<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Asset;
use App\Models\Manufacturer;
use App\Models\Location;
use App\Models\User;
use App\Models\Operator;
use App\Models\Driver;
use App\Models\Motorcycle;
use App\Models\Toda;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(){
        return inertia('Dashboard');
    }

    public function stats(){
        // Fetch total counts for various entities
        $totalAssets = Asset::count(); // Count total assets in the database
        $totalCategories = Category::count(); // Count total categories in the database
        $totalManufacturers = Manufacturer::count(); // Count total manufacturers in the database
        $totalLocations = Location::count(); // Count total locations in the database
        $totalUsers = User::count(); // Count total users in the database

        // Fetch operator management statistics
        $totalOperators = Operator::count();
        $totalDrivers = Driver::count();
        $totalMotorcycles = Motorcycle::count();
        $totalToda = Toda::count();

        // Fetch operator counts by permit status
        $operatorsByStatus = Operator::select('permit_status', DB::raw('count(*) as total'))
            ->groupBy('permit_status')
            ->get();

        // Calculate permit status counts
        $newPermits = Operator::where('permit_status', 'new')->count();
        $renewPermits = Operator::where('permit_status', 'renew')->count();
        $retirePermits = Operator::where('permit_status', 'retire')->count();

        // Fetch operators by TODA
        $operatorsByToda = Operator::select('toda_id', DB::raw('count(*) as total'))
            ->groupBy('toda_id')
            ->with('toda:id,toda_name')
            ->get();

        // Fetch recent operators for the dashboard table
        $recentOperators = Operator::with(['driver', 'motorcycle', 'toda'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'fullname' => $operator->fullname,
                    'email_address' => $operator->email_address,
                    'contact_number' => $operator->contact_number,
                    'driver_fullname' => $operator->driver->driver_fullname ?? 'N/A',
                    'plate_number' => $operator->motorcycle->plate_number ?? 'N/A',
                    'mtop_number' => $operator->mtop_number,
                    'toda_name' => $operator->toda->toda_name ?? 'N/A',
                    'date_registered' => $operator->date_registered,
                    'permit_status' => $operator->permit_status,
                    'date_last_paid' => $operator->date_last_paid,
                ];
            });

        // Fetch asset counts grouped by status (keep for reference if needed)
        $assetsByStatus = Asset::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Fetch asset counts grouped by category
        $assetsByCategory = Asset::select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get();

        // Fetch asset counts grouped by location
        $assetsByLocation = Asset::select('location_id', DB::raw('count(*) as total'))
            ->groupBy('location_id')
            ->with('location:id,name')
            ->get();

        // Fetch asset counts grouped by assigned user
        $assetsByAssignedUser = Asset::select('assigned_to_user_id', DB::raw('count(*) as total'))
            ->groupBy('assigned_to_user_id')
            ->with('assignedTo:id,name')
            ->get();

        // Return the data as a JSON response
        return response()->json([
            'totals' => [
                'total_assets' => $totalAssets,
                'total_categories' => $totalCategories,
                'total_manufacturers' => $totalManufacturers,
                'total_locations' => $totalLocations,
                'total_users' => $totalUsers,
                // Operator management totals
                'total_operators' => $totalOperators,
                'total_drivers' => $totalDrivers,
                'total_motorcycles' => $totalMotorcycles,
                'total_toda' => $totalToda,
                'new_permits' => $newPermits,
                'renew_permits' => $renewPermits,
                'retire_permits' => $retirePermits,
            ],
            'charts' => [
                'assets_by_status' => $assetsByStatus,
                'assets_by_category' => $assetsByCategory,
                'assets_by_location' => $assetsByLocation,
                'assets_by_assigned_user' => $assetsByAssignedUser,
                // Operator management charts
                'operators_by_status' => $operatorsByStatus,
                'operators_by_toda' => $operatorsByToda,
            ],
            'operators_list' => $recentOperators,
        ]);
    }

    /**
     * Get operator statistics specifically for the operator management dashboard
     */
    public function operatorStats()
    {
        $totalOperators = Operator::count();
        $totalDrivers = Driver::count();
        $totalMotorcycles = Motorcycle::count();
        $totalToda = Toda::count();

        $newPermits = Operator::where('permit_status', 'new')->count();
        $renewPermits = Operator::where('permit_status', 'renew')->count();
        $retirePermits = Operator::where('permit_status', 'retire')->count();

        // Get operators by TODA for chart data
        $operatorsByToda = Toda::withCount('operators')->get()->map(function ($toda) {
            return [
                'toda_name' => $toda->toda_name,
                'total' => $toda->operators_count
            ];
        });

        // Get operators by permit status for chart data
        $operatorsByStatus = Operator::select('permit_status', DB::raw('count(*) as total'))
            ->groupBy('permit_status')
            ->get();

        return response()->json([
            'total_operators' => $totalOperators,
            'total_drivers' => $totalDrivers,
            'total_motorcycles' => $totalMotorcycles,
            'total_toda' => $totalToda,
            'new_permits' => $newPermits,
            'renew_permits' => $renewPermits,
            'retire_permits' => $retirePermits,
            'charts' => [
                'operators_by_toda' => $operatorsByToda,
                'operators_by_status' => $operatorsByStatus,
            ]
        ]);
    }

    /**
     * Get recent operators for dashboard table
     */
    public function recentOperators()
    {
        $operators = Operator::with(['driver', 'motorcycle', 'toda'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'fullname' => $operator->fullname,
                    'email_address' => $operator->email_address,
                    'contact_number' => $operator->contact_number,
                    'driver_fullname' => $operator->driver->driver_fullname ?? 'N/A',
                    'plate_number' => $operator->motorcycle->plate_number ?? 'N/A',
                    'mtop_number' => $operator->mtop_number,
                    'toda_name' => $operator->toda->toda_name ?? 'N/A',
                    'date_registered' => $operator->date_registered->format('Y-m-d'),
                    'permit_status' => $operator->permit_status,
                    'date_last_paid' => $operator->date_last_paid ? $operator->date_last_paid->format('Y-m-d') : null,
                    'created_at' => $operator->created_at,
                ];
            });

        return response()->json($operators);
    }

    /**
     * Get dashboard overview with both asset and operator statistics
     */
    public function dashboardOverview()
    {
        // Asset statistics
        $assetStats = [
            'total_assets' => Asset::count(),
            'total_categories' => Category::count(),
            'total_manufacturers' => Manufacturer::count(),
            'total_locations' => Location::count(),
        ];

        // Operator statistics
        $operatorStats = [
            'total_operators' => Operator::count(),
            'total_drivers' => Driver::count(),
            'total_motorcycles' => Motorcycle::count(),
            'total_toda' => Toda::count(),
            'new_permits' => Operator::where('permit_status', 'new')->count(),
            'renew_permits' => Operator::where('permit_status', 'renew')->count(),
            'retire_permits' => Operator::where('permit_status', 'retire')->count(),
        ];

        // Recent operators for table
        $recentOperators = Operator::with(['driver', 'motorcycle', 'toda'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'fullname' => $operator->fullname,
                    'driver_fullname' => $operator->driver->driver_fullname ?? 'N/A',
                    'plate_number' => $operator->motorcycle->plate_number ?? 'N/A',
                    'toda_name' => $operator->toda->toda_name ?? 'N/A',
                    'permit_status' => $operator->permit_status,
                    'date_registered' => $operator->date_registered->format('M d, Y'),
                ];
            });

        return response()->json([
            'asset_stats' => $assetStats,
            'operator_stats' => $operatorStats,
            'recent_operators' => $recentOperators,
        ]);
    }
}
