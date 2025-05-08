<script setup>

</script>

<template>
    <BaseSlide next="/slides/4" previous="/slides/2">
        <template #title>What are we going to cover today?</template>
        <template #content>
            <div class="flex flex-col items-center">
                <ul class="space-y-4 text-center text-3xl">
                    <li>Project setup</li>
                    <li>Talking to local Ollama using Prism</li>
                    <li>Creating functions to be used as tools in prompts</li>
                    <li>RAG - Retrieval Augmented Generation </li>
                    <li>Prism Server</li>
                </ul>
            </div>
        </template>
    </BaseSlide>
</template>
