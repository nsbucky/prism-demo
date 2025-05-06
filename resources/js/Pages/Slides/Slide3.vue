<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/4" previous="/slides/2">
        <template #title>What are we going to cover today?</template>
        <template #content>

            <div class="flex">
                <div class="w-1/2">
                    <ul class="space-y-3">
                        <li>Laravel framework</li>
                        <li>Prism library</li>
                        <li>Local Ollama setup using Docker</li>
                        <li>Use Laravel's Sail</li>

                    </ul>
                </div>

                <div class="w-1/2">
                    <ul class="space-y-3">
                        <li>Talking to the Llama</li>
                        <li>Using tools and function calls in prompts.</li>
                        <li>RAG with Weird Al</li>
                        <li>MCP with Prism Server</li>
                    </ul>
                </div>
            </div>
        </template>
    </BaseSlide>
</template>


