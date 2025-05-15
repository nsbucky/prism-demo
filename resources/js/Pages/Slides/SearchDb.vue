<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `$promptEmbeddingResponse = Prism::embeddings()
->using(Provider::Ollama, 'mxbai-embed-large')
->fromInput($normalizedPrompt)
->asEmbeddings();

$embeddingArray = $promptEmbeddingResponse->embeddings[0]->embedding;
$formattedEmbedding = '[' . implode(',', $embeddingArray) . ']';

Lyric::query()
->select(['id', 'name', 'original_text'])
->orderByRaw('embedding <=> ?::vector', [$formattedEmbedding])
->limit(1)
->first();`
</script>

<template>
    <BaseSlide>
        <template #title>Semantic Vector Search</template>
        <template #content>
            <p class="mb-3">Take the user's prompt, clean it up, then ask the LLM to create a similar embedding of this prompt.
                In Postgres you can use the <code><=></code> cosine distance operator to search for related items. </p>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />

        </template>

        <template #footer>
            <div class="flex flex-col w-1/2 mx-auto">
                <code class="bg-gray-800/20 p-2 rounded-lg">
                    https://github.com/pgvector/pgvector
                </code>
            </div>
        </template>
    </BaseSlide>
</template>
