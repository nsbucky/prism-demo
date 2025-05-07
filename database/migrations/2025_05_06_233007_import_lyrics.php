<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        if(!File::exists(database_path('migrations/lyrics.sql'))) {
            return;
        }

        $sql = File::get(database_path('lyrics.sql'));

        DB::connection('pgsql')->unprepared($sql);
    }
};
