<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
import Weird from '@/assets/accordion.jpg'
</script>

<template>
    <BaseSlide next="/slides/10" previous="/slides/8">
        <template #title>🎼 🎵 🎶 Weird AI Parody Songs 🎵🎵 </template>
        <template #content>
          <div class="flex items-center">



            <div>
              <h3 class="text-pink-400 text-3xl mb-3">Let's write silly songs!</h3>

              <p class="mb-3">Given an initial user prompt:</p>

              <ol class="list-decimal list-inside space-y-2">
                <li>Perform some pre-processing on the prompt</li>
                <li>Extract prompt keywords</li>
                <li>Search vector database for related songs</li>
                <li>Combine context into 1 large prompt</li>
                <li>Create a parody song in the style of Weird Al?</li>
              </ol>

            </div>

            <div style="width: 400px" class=" ml-4">
              <img :src=Weird alt="Accordion" class="bg-white rounded-lg shadow-lg p-4 mb-2 w-full">
            </div>
          </div>

        </template>
    </BaseSlide>
</template>
