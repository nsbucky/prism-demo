<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `Pipeline::send($this->userPrompt)
   ->through([
       // Lowercase
       fn($input) => mb_strtolower($input),

       // Remove stop words
       fn($input) => preg_replace('/\\b(?:the|a|is|and)\\b/', '', $input),

       // Remove punctuation
       fn($input) => preg_replace('/[^\\w\\s]/u', '', $input),

       // Remove special characters
       fn($input) => preg_replace('/[^\\p{L}\\p{N}\\s]/u', '', $input),
   ])
   ->then(fn($userPrompt) => $userPrompt);`
</script>

<template>
    <BaseSlide>
        <template #title>Prompt Preparation</template>
        <template #content>

            <p class="mb-2">Embedding models aim to capture the meaning of text. Noise like stop words,
                capitalization differences, and punctuation can sometimes obscure the core semantic content, leading to
                less
                accurate similarity comparisons with your document embeddings. Normalizing the data will help keep
                results consistent and safe.</p>

            <h3 class="text-orange-500 font-bold mb-3">Use Laravel's Pipeline class:</h3>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />


        </template>
    </BaseSlide>
</template>
