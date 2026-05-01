<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'employer') {
            return response()->json(['message' => 'Only employers can access dashboard stats'], 403);
        }

        $listings = JobPosting::query()
            ->where('user_id', $user->id)
            ->withCount('applications')
            ->get(['id', 'title']);

        $jobIds = $listings->pluck('id');

        $recentApplications = JobApplication::query()
            ->whereIn('job_id', $jobIds)
            ->with(['user:id,name', 'jobPosting:id,title'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (JobApplication $application): array {
                return [
                    'applicant_name' => $application->user?->name,
                    'job_title' => $application->jobPosting?->title,
                    'status' => $application->status,
                    'created_at' => $application->created_at,
                ];
            });

        return response()->json([
            'my_listings_count' => $listings->count(),
            'total_applications_received' => JobApplication::query()->whereIn('job_id', $jobIds)->count(),
            'listings' => $listings->map(fn (JobPosting $jobPosting): array => [
                'id' => $jobPosting->id,
                'title' => $jobPosting->title,
                'applications_count' => $jobPosting->applications_count,
            ])->values(),
            'recent_applications' => $recentApplications,
        ]);
    }
}
