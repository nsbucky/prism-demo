<script setup>

import PrismLogo from '@/assets/prism.png'
import SlideBody from "../Components/SlideBody.vue";

</script>

<template>

  <BaseSlide next="/slides/6" previous="/slides/4">

    <template #title>use Prism\Prism\Prism;</template>

    <template #content>
      <div class="grid grid-cols-6">
        <div class="col-span-3 p-4">
          <img :src=PrismLogo alt="Prism Logo" class="bg-white rounded-lg shadow-lg p-4 mb-2 w-full" width="573"
               height="294"/>
          <a href="https://prismphp.com/" target="_blank" rel="noopener noreferrer">Prism PHP</a>
          |
          <a href="https://prismphp.com/getting-started/introduction.html/" target="_blank" rel="noopener noreferrer">Docs</a>
        </div>

        <div class="col-span-3 p-8">
          <SlideBody>

            <p class="mb-2">
              <b>Unified Provider Interface:</b> simple to switch between a variety of AI providers.
            </p>

            <p class="mb-2"><b>Tool System:</b> write PHP code to define tools that can be used by the AI Provider <span
                class="text-pink-400">(scary!)</span>
              </p>

            <p class="mb-2"><b>Image Support:</b> some LLMs support image generation and analysis, Prism can handle this
              too. <span class="text-orange-300">(neat!)</span>
            </p>

          </SlideBody>

        </div>
      </div>

    </template>

    <template #footer>
      <div class="flex flex-col w-1/2 mx-auto">
        <p class="text-center">Simple install</p>
        <code class="bg-gray-800/20 p-2 rounded-lg">
          composer require prism-php/prism<br>
          php artisan vendor:publish --tag=prism-config
        </code>
      </div>
    </template>
  </BaseSlide>

</template>
