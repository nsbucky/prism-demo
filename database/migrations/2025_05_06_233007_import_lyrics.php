<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        if(!File::exists(database_path('lyrics.sql'))) {
            return;
        }

        // truncate table
        DB::connection('pgsql')->unprepared('TRUNCATE TABLE lyrics');

        $sql = File::get(database_path('lyrics.sql'));

        DB::connection('pgsql')->unprepared($sql);
    }
};
