<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/9" previous="/slides/7">
        <template #title>RAG - Retrieval Augmented Generation </template>
        <template #content>
            Use semantic search to find relevant documents and use them as context for your prompt.

            **Thoughtfully Create Really Excellent Inputs** aka TCREI - google prompting course on coursera

            RAG is a machine learning technique that incorporates external knowledge into a prompt sent to an LLM. The model’s response is based not only on its internal knowledge but also on the additional information provided in the prompt.

            prompt -> pre-filter/prep/guardrails/formatting -> query to vector database ->return matching documents ->prompt + matching docs used as context -> better (hopefully) response

            Link to video on vector embedding
            https://www.youtube.com/watch?v=gl1r1XV0SLw&t=6s

            **Explain how Prism handles embeddings**
            TextEmbeddingCommand
        </template>
    </BaseSlide>
</template>
