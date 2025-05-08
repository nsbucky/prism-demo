<script setup>

import YtIcon from "@/assets/youtube.svg";
</script>

<template>
  <BaseSlide next="/slides/9" previous="/slides/7">
    <template #title>RAG - Retrieval Augmented Generation</template>
    <template #content>

      <p>RAG is a machine learning technique that incorporates external knowledge into a prompt sent to an LLM.
        The model’s response is based not only on its internal knowledge but also on the additional information provided
        in the prompt.</p>

      <p>Prism comes with functionality to generate AI embeddings that you can store in a vector database.
        This enables semantic search, allowing us to find documents relevant to the original prompt. </p>

      <p><span class="text-pink-400 font-bold">Embeddings</span> consist of arrays of floating point numbers where each
        position represents some kind of learned
        feature from the input given. </p>

      <code class="mb-6 bg-gray-800/20 rounded p-2">[0.048100714, -0.0482884, 0.0136552155, -0.01290639, ...]</code>

      <hr class="my-6">


      <h4 class="text-2xl font-bold text-pink-400">Initial User Prompt</h4>

      <div>
        <span class="text-orange-500 ml-2">&rightarrow;</span>
        Pre-filtering, pre-processing, guardrails, formatting<br>
        <span class="text-orange-500  ml-4">&rightarrow;</span>
                Find semantically related documents in vector datastore<br>
        <span class="text-orange-500 ml-6">&rightarrow;</span>
                User prompt + matching documents used as context<br>

        <span class="text-orange-500 ml-8">&equals;</span>
        <span class="text-2xl text-yellow-400 my-2 ml-2">Better (hopefully) response</span>
      </div>

      <div class="my-2 text-center">
        <a href="https://youtu.be/gl1r1XV0SLw?si=RbajQ4ARJ7bByeON&t=139" target="_blank">
          <img :src=YtIcon alt="YouTube Icon" class="bg-white rounded-lg shadow-lg p-2 inline-block" width="50" />
          Vector Databases and RAG video
        </a>
      </div>

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
