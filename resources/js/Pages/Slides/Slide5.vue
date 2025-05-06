<script setup>
import {ref, markRaw} from 'vue'
import BaseSlide from "../Components/BaseSlide.vue";
import PrismLogo from '@/assets/prism.png'
import SlideTitle from "../Components/SlideTitle.vue";
import SlideBody from "../Components/SlideBody.vue";

const slides = ref([
    {
        title: 'AI Providers',
        content: 'Prism supports multiple AI providers allowing you can switch between them seamlessly without changing your application code.',
    },
    {
        title: 'AI Providers',
        content: '    Anthropic\n' +
            '    DeepSeek\n' +
            '    Groq\n' +
            '    Mistral\n' +
            '    Ollama\n' +
            '    OpenAI\n' +
            '    xAI'
    },
])
</script>

<template>

    <BaseSlide next="/slides/6" previous="/slides/4">

        <template #title>Prism Package</template>

        <template #content>
            <div class="grid grid-cols-6">
                <div class="col-span-3 p-4">
                    <img :src=PrismLogo alt="Prism Logo" class="bg-white rounded-lg shadow-lg p-4 mb-2 w-full" width="573" height="294"/>
                </div>

                <div class="col-span-3 p-8">
                    <SlideBody>

                    <p class="mb-2">
                        <b>Unified Provider Interface:</b> Switch seamlessly between AI providers like OpenAI,
                        Anthropic,
                        and Ollama without changing your application code.
                    </p>

                    <p class="mb-2"><b>Tool System:</b> Extend AI capabilities by defining custom tools that can interact with your
                        application's business logic.</p>

                    <p class="mb-2"><b>Image Support:</b> Work with multi-modal models that can process both text and images.</p>
                    </SlideBody>
                </div>
            </div>


        </template>
    </BaseSlide>
</template>
