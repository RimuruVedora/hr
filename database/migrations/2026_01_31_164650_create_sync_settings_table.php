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
        Schema::create('sync_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sync_mode')->default('manual'); // manual, auto
            $table->string('remote_url')->nullable();
            $table->string('api_token')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable(); // success, failed
            $table->text('last_sync_message')->nullable();
            $table->timestamps();
        });

        // Insert default record
        DB::table('sync_settings')->insert([
            'sync_mode' => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_settings');
    }
};
