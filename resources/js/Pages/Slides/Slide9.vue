<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/10" previous="/slides/8">
        <template #title>Weird AI</template>
        <template #content>
            I decided to use Weird Al lyrics as my context for a RAG implementation. expand on that.

          <p>In this example, we will use the Ollama LLM to generate embeddings for a set of Weird Al lyrics.
            We will then use these embeddings to find the most relevant lyrics for a given prompt.</p>
        </template>
    </BaseSlide>
</template>
