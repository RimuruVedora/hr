<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ess_requests', function (Blueprint $table) {
            $table->string('response_file_name')->nullable();
            $table->string('response_file_mime')->nullable();
        });

        // Add LONGBLOB column using raw SQL for MySQL
        DB::statement("ALTER TABLE ess_requests ADD response_file_data LONGBLOB");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ess_requests', function (Blueprint $table) {
            $table->dropColumn(['response_file_name', 'response_file_mime']);
        });

        DB::statement("ALTER TABLE ess_requests DROP COLUMN response_file_data");
    }
};
