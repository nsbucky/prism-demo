<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mureka_creations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained('songs');
            $table->string('mureka_id')->unique();
            $table->string('model');
            $table->string('status');
            $table->string('failed_reason')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('choices')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mureka_creations');
    }
};
