<script setup>

//import YtIcon from "@/assets/youtube.svg";
</script>

<template>
  <BaseSlide>
    <template #title>RAG - Retrieval Augmented Generation</template>
    <template #content>

      <p>RAG is a machine learning technique that incorporates external knowledge into a prompt sent to an LLM.
        The model’s response is based not only on its internal knowledge but also on the additional information provided
        in the prompt.</p>

      <h4 class="text-2xl font-bold text-pink-400 my-4">Initial User Prompt</h4>

      <div class="mb-3">
        <span class="text-orange-500 ml-2">&rightarrow;</span>
        Pre-filtering, pre-processing, guardrails, formatting<br>
        <span class="text-orange-500  ml-4">&rightarrow;</span>
                Find semantically related documents in vector datastore<br>
        <span class="text-orange-500 ml-6">&rightarrow;</span>
                User prompt + matching documents used as context<br>

        <span class="text-orange-500 ml-8">&equals;</span>
        <span class="text-2xl text-yellow-400 my-2 ml-2">Better (hopefully) response</span>
      </div>

    </template>
  </BaseSlide>
</template>

<style scoped>
p {
  margin-bottom: 1rem;
}
</style>
