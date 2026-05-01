<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\JobApplication;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'company',
        'location',
        'salary',
        'category',
        'user_id',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A job posting has many applications
    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'job_listing_id');
    }
}
