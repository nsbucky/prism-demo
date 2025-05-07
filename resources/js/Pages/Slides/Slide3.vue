<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/4" previous="/slides/2">
        <template #title>What are we going to cover today?</template>
        <template #content>
            <div class="flex flex-col items-center">
                <ul class="space-y-3 text-center">
                    <li>Project setup (switch to IDE)</li>
                    <li>Talking to local Ollama using Prism</li>
                    <li>Creating functions to be used as tools in prompts.</li>
                    <li>RAG with Weird Al</li>
                    <li>MCP with Prism Server</li>
                </ul>
            </div>
        </template>
    </BaseSlide>
</template>


