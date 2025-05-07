<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('pgsql')
              ->create('lyrics', function (Blueprint $table) {
                  $table->id();

                  $table->string('name');

                  /*
                   * 1024 because it is the default for pgvector.
                   */
                  $table->vector('embedding', 1024)
                        ->comment('The embedding vector for the document, used for similarity search');

                  $table->text('original_text')
                        ->comment('The original text of the document');

                  $table->timestamps();
              });

        DB::connection('pgsql')
          ->statement('
            CREATE INDEX lyrics_embedding_ivfflat_index
            ON lyrics
            USING ivfflat (embedding vector_cosine_ops)
            WITH (lists = 1000);
        ');

    }
};
