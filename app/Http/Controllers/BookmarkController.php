<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobListingResource;
use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->with('jobListing.user')
            ->get();

        return response()->json([
            'data' => JobListingResource::collection($bookmarks->pluck('jobListing')->filter()->values())->resolve(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'job_listing_id' => 'required|exists:job_postings,id',
        ]);

        $exists = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('job_listing_id', $request->job_listing_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Job already bookmarked'], 409);
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'job_listing_id' => $request->job_listing_id,
        ]);

        return response()->json(['message' => 'Job bookmarked'], 201);
    }

    public function destroy(Request $request, int $job_listing_id)
    {
        Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('job_listing_id', $job_listing_id)
            ->delete();

        return response()->json(['message' => 'Bookmark removed']);
    }
}
