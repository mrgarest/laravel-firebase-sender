<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
||-------------------------------------------------------------------------------------||
||-------------------------------------------------------------------------------------||
|| DO NOT MAKE ANY CHANGES IF YOU WANT TO USE THIS MIGRATION USING THE STANDARD METHOD ||
||-------------------------------------------------------------------------------------||
||-------------------------------------------------------------------------------------||
*/

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firebase_sender_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id');
            $table->string('project_id');
            $table->boolean('high_priority');
            $table->string('type', 50);
            $table->string('to');
            $table->string('value')->nullable()->default(null);
            $table->timestamp('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firebase_sender_logs');
    }
};
