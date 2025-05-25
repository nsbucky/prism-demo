<script setup>
import OllamaImg from "@/assets/ollama.png";
import HuggingFaceImg from "@/assets/hf-logo.svg";
</script>

<template>
    <BaseSlide>
        <template #title>Ollama LLM Setup</template>
        <template #content>

            <div class="grid grid-cols-4 gap-8 mb-8">
                <div>
                    <img :src="OllamaImg" alt="Ollama Logo"
                         class="bg-white rounded-lg shadow-lg p-2 mb-2 mx-auto"
                    style="height: 125px"/>

                </div>
                <div class="col-span-3">
                    <p>Run Llama 3.2, DeepSeek-R1, Qwen 3, Mistral, Gemma 3, and other models, locally.</p>
                    <code class="bg-gray-800/20 p-4">ollama run llama3.2</code>
                    <a href="https://ollama.com" class="text-orange-300">ollama.com</a>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-8 mb-4">
                <div class="text-center basis-1/3">
                    <img :src="HuggingFaceImg" alt="Huggy Boy"
                         class="bg-white rounded-lg shadow-lg p-1 mb-2 mx-auto"
                         style="height: 125px"/>
                </div>
                <div class="col-span-3">
                    <p>The Hugging Face Hub works as a central place where anyone can share, explore, discover, and experiment with open-source ML.</p>

                    <a href="https://huggingface.co/models" class="text-orange-300">huggingface.co</a>

                </div>
            </div>

            <p class="text-center">This project uses llama3.2, qwen3:4b, and mxbai-embed-large models.</p>

        </template>
        <template #footer>
            <div class="flex flex-col w-1/2 mx-auto">
                <code class="bg-gray-800/20 p-2 rounded-lg">
                    docker exec -it ollama /bin/bash<br>
                    ollama ps<br>
                    ollama list <br>
                </code>
            </div>
        </template>
    </BaseSlide>
</template>
<style scoped>
p {
    margin-bottom: 1rem;
}
code {
    margin-bottom: 1rem;
    display: block;
}
</style>
