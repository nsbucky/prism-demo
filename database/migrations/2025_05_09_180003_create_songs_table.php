<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('keywords')->nullable();
            $table->text('lyrics');
            $table->text('prompt');
            $table->text('formatted_prompt');
            $table->json('matched_lyrics')->nullable();
            $table->timestamps();
        });
    }
};
