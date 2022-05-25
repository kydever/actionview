<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnsToActivityTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity', function (Blueprint $table) {
            $table->json('data');
            $table->string('event_key', 64);
            $table->json('issue');
            $table->unsignedBigInteger('issue_id');
            $table->json('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity', function (Blueprint $table) {
            $table->dropColumn('data', 'event_key', 'issue', 'issue_id', 'user');
        });
    }
}
