<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApplicationResource;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    /**
     * Display all job applications
     */
    public function index(Request $request)
    {
        $applications = JobApplication::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return ApplicationResource::collection($applications);
    }

    /**
     * Store a newly created job application in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'cover_letter' => 'required',
            'resume' => 'nullable|mimes:pdf|max:2048',
        ]);

        $user = $request->user();
        if ($user->role !== 'job_seeker') {
            return response()->json(['message' => 'Only job seekers can apply to jobs'], 403);
        }

        $alreadyApplied = JobApplication::query()
            ->where('user_id', $user->id)
            ->where('job_id', $request->job_id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json(['message' => 'You have already applied to this job'], 409);
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        }

        $application = JobApplication::create([
            'job_id' => $request->job_id,
            'user_id' => $user->id,
            'cover_letter' => $request->cover_letter,
            'status' => 'pending',
            'resume_path' => $resumePath,
        ]);

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a specific job application
     */
    public function show(Request $request, JobApplication $jobApplication)
    {
        if ($jobApplication->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new ApplicationResource($jobApplication);
    }

    /**
     * Update a specific job posting in storage.
     */
    public function update(Request $request, JobApplication $jobApplication)
    {
        $request->validate([
            'cover_letter' => 'required',
        ]);

        if ($jobApplication->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $jobApplication->update([
            'cover_letter' => $request->cover_letter,
        ]);

        return new ApplicationResource($jobApplication);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'reviewed', 'accepted', 'rejected'])],
        ]);

        $application = JobApplication::findOrFail($id);
        $jobPosting = JobPosting::findOrFail($application->job_id);

        if ($jobPosting->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $application->update(['status' => $request->status]);

        return new ApplicationResource($application);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, JobApplication $jobApplication)
    {
        if ($jobApplication->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $jobApplication->delete();

        return response()->json(null, 204);
    }
}
