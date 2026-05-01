<?php
namespace App\Http\Controllers;

use App\Http\Resources\JobListingResource;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobPostingController extends Controller
{
    /**
     * Display all the job listings
     */
    public function index(Request $request)
    {
        $request->validate([
            'sortBy' => ['nullable', Rule::in(['title', 'salary', 'created_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = JobPosting::query()->with('user')->active();

        //filter by job category
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        //filter by job location
        if ($request->has('location')) {
            $query->where('location', $request->input('location'));
        }
 //filter by job title
        if ($request->has('title')) {
            $query->where('title', $request->input('title'));
        }

        //filter by salary range
        if ($request->has('salary_min')) {
            $query->where('salary', '>=', $request->input('salary_min'));
        }

        if ($request->has('salary_max')) {
            $query->where('salary', '<=', $request->input('salary_max'));
        }

        if ($request->has('salary')) {
            $query->where('salary', $request->input('salary'));
        }

        $sortBy = $request->input('sortBy', 'created_at');
        $order = $request->input('order', 'desc');
        $postings = $query->orderBy($sortBy, $order)->paginate(10);

        return response()->json([
            'data' => JobListingResource::collection($postings->getCollection())->resolve(),
            'current_page' => $postings->currentPage(),
            'last_page' => $postings->lastPage(),
            'per_page' => $postings->perPage(),
            'total' => $postings->total(),
        ]);
    }

    /**
     * Store a newly created job listing in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'company' => 'required',
            'location' => 'required',
            'salary' => 'required',
            'category' => 'required',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $this->authorize('create', JobPosting::class);

        $posting = JobPosting::create([
            ...$request->only(['title', 'description', 'company', 'location', 'salary', 'category', 'expires_at']),
            'user_id' => $request->user()->id,
            'is_active' => true,
        ]);

        return (new JobListingResource($posting->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified listing with the applications.
     */
    public function show(JobPosting $jobPosting)
    {
        return new JobListingResource($jobPosting->load('applications', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobPosting $jobPosting)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'company' => 'required',
            'location' => 'required',
            'salary' => 'required',
            'category' => 'required',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($jobPosting->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->authorize('update', $jobPosting);

        $jobPosting->update($request->only([
            'title',
            'description',
            'company',
            'location',
            'salary',
            'category',
            'expires_at',
        ]));

        return new JobListingResource($jobPosting->load('user'));
    }

    /**
     * Remove the specified job listing from storage.
     */
    public function destroy(Request $request, JobPosting $jobPosting)
    {
        if ($jobPosting->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->authorize('delete', $jobPosting);

        $jobPosting->delete();

        return response()->json(null, 204);
    }

    public function restore(Request $request, int $id)
    {
        $jobPosting = JobPosting::withTrashed()->findOrFail($id);

        $this->authorize('restore', $jobPosting);
        $jobPosting->restore();

        return new JobListingResource($jobPosting->load('user'));
    }
}
