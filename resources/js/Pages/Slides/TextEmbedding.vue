<script setup>

import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `Prism::embeddings()
    ->using(Provider::Ollama, 'mxbai-embed-large')
    ->fromInput('What is the best city for spatulas?')
    ->asEmbeddings();`
</script>

<template>
  <BaseSlide>
    <template #title>Prism Text Embedding</template>
    <template #content>

      <p>Prism comes with functionality to generate AI embeddings that you can store in a vector database, such as Postgres.
          You can then use Laravel's Eloquent to search your database, allowing us to find documents relevant to the original prompt. </p>

      <p><span class="text-pink-400 font-bold ">Embeddings</span> consist of arrays of floating point numbers where each
        position represents some kind of learned
        feature from the input given. </p>

        <p>For example, the words "spatula" and "flipper" may not be similar in terms of their spelling or pronunciation,
            but they are semantically similar because they refer to the same type of object.</p>

      <code class="mb-6 bg-gray-800/20 rounded p-2 mb-3 block">[0.048100714, -0.0482884, 0.0136552155, -0.01290639, ...]</code>

        <VueCodeBlock highlightjs lang="php" :code=sampleCode />



<!--      <div class="my-2">
        <a href="https://youtu.be/gl1r1XV0SLw?si=RbajQ4ARJ7bByeON&t=139" target="_blank">
          <img :src=YtIcon alt="YouTube Icon" class="bg-white rounded-lg shadow-lg p-2 inline-block" width="50" />
          Vector Databases and RAG video
        </a>
      </div>-->

    </template>

    <template #footer>
      <div class="flex flex-col w-1/2 mx-auto">
        <code class="bg-gray-800/20 p-2 rounded-lg">
          ollama:text-embed
        </code>
      </div>
    </template>
  </BaseSlide>
</template>

<style scoped>
p {
  margin-bottom: 1rem;
}
</style>
