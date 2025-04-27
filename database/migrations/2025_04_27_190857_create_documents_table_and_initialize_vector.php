<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('pgsql')
              ->create('documents', function (Blueprint $table) {
                  $table->id();

                  $table->string('name');

                  /*
                   * From co-pilot
                   * The dimension size of 1536 for the embedding column was likely chosen because it matches the output
                   * dimensionality of the mxbai-embed-large model used in the TextEmbeddingCommand. Embedding models typically
                   * produce fixed-size vectors, and 1536 is a common dimensionality for large language models or embedding models.
                   * This ensures compatibility between the model's output and the database schema.
                   */
                  $table->vector('embedding', 1536)
                        ->index('documents_embedding_index')
                        ->algorithm('hnsw')
                        ->distance('cosine')
                        ->comment('The embedding vector for the document, used for similarity search');

                  $table->text('original_text')
                        ->comment('The original text of the document');

                  $table->timestamps();
              });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
