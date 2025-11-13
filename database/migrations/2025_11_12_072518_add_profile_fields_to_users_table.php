<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
             $table->string('display_name')->nullable()->after('name');
            $table->text('bio')->nullable()->after('display_name');
            $table->string('avatar')->nullable()->after('bio');
            $table->string('status')->default('offline')->after('avatar'); // online, idle, dnd, offline
            $table->timestamp('last_seen_at')->nullable()->after('status');
            $table->string('theme')->default('dark')->after('last_seen_at'); // dark, light
            $table->json('settings')->nullable()->after('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
            'display_name', 'bio', 'avatar', 'status', 
            'last_seen_at', 'theme', 'settings'
            ]);
        });
    }
};
