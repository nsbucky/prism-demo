<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
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

      <h4 class="text-2xl font-bold text-pink-300">Workflow</h4>
      <ol>
        <li>
          User prompt
        </li>
        <li>
          Pre-filtering, pre-processing, guardrails, formatting
        </li>
        <li>
          Query to vector database
        </li>
        <li>
          Return matching documents
        </li>
        <li>
          User prompt + matching documents used as context
        </li>
        <li>
          Better (hopefully) response
        </li>
      </ol>

      <p>
        <a href="https://www.youtube.com/watch?v=gl1r1XV0SLw&t=6s" target="_blank">
          <img :src=YtIcon alt="YouTube Icon" class="bg-white rounded-lg shadow-lg p-2 inline-block" width="50" />
          Vector Databases and RAG video
        </a>
      </p>

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
