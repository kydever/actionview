<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddDescriptionToOswfDefinitionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('oswf_definition', function (Blueprint $table) {
            $table->string ( 'description', 50 )->default('')->after('contents')->comment ( '描述' );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oswf_definition', function (Blueprint $table) {
            $table->dropColumn ( 'description' );
        });
    }
}
