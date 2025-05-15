<script setup>

</script>

<template>
    <BaseSlide>
        <template #title>What are we going to cover today?</template>
        <template #content>
            <div class="flex flex-col items-center">
                <ul class="space-y-4 text-center text-3xl">
                    <li>Project setup</li>
                    <li>Talking to local Ollama using Prism</li>
                    <li>Creating functions to be used as tools in prompts</li>
                    <li>RAG - Retrieval Augmented Generation</li>
                    <li>Prism Server</li>
                </ul>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col w-1/2 mx-auto">
                <div class="text-center">
                <a href="https://ollama.com" target="_blank" class="text-orange-300 mr-4">Ollama</a>

                <a href="https://huggingface.co/" target="_blank" class="text-orange-300">Hugging Face</a>
                </div>
            </div>
        </template>
    </BaseSlide>
</template>
