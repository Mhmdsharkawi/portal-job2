<?php

namespace App\Console\Commands;

use App\Models\JobPosting;
use Illuminate\Console\Command;

class ArchiveExpiredJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:archive-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive expired job listings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = JobPosting::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['is_active' => false]);

        $this->info("Archived {$count} expired job listings.");

        return self::SUCCESS;
    }
}
