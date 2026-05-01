<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'cover_letter',
        'status',
        'resume_path',
    ];

    // A job application belongs to a user (the applicant)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A job application belongs to a job posting
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }
}
