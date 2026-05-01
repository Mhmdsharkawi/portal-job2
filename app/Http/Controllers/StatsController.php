<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;

class StatsController extends Controller
{
    public function index()
    {
        $applicationsByStatus = JobApplication::query()
            ->get()
            ->groupBy('status')
            ->map->count();

        $topCategories = JobPosting::query()
            ->active()
            ->get();

        $topCategories = $topCategories
            ->groupBy('category')
            ->map(fn ($group, $category) => ['category' => $category, 'count' => $group->count()])
            ->sortByDesc('count')
            ->take(5)
            ->values();

        return response()->json([
            'total_listings' => JobPosting::query()->active()->count(),
            'total_applications' => JobApplication::query()->count(),
            'applications_by_status' => $applicationsByStatus,
            'top_categories' => $topCategories,
            'average_salary' => round((float) JobPosting::query()->active()->avg('salary'), 2),
            'total_users' => User::query()->count(),
        ]);
    }
}
