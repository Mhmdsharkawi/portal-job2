<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE job_applications MODIFY status ENUM('pending','reviewed','accepted','rejected') DEFAULT 'pending'");
        }

        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('resume_path')->nullable()->after('status');
            $table->unique(['user_id', 'job_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('job_applications_user_id_job_id_unique');
            $table->dropColumn('resume_path');
        });
    }
};
