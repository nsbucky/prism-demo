<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/11" previous="/slides/9">
        <template #title>Prism MCP Server</template>
        <template #content>
            Make a demo of a simple prism server so people can understand the concepts of MCP and how they can use it in their project. integrate it with Open Web UI!
            Prism Server acts as a middleware, translating requests from OpenAI-compatible clients into Prism-specific operations. This means you can use tools like ChatGPT web UIs or any OpenAI SDK to interact with your custom Prism models.
        </template>
    </BaseSlide>
</template>
