<script setup>

import llamaImage from '../../assets/llama-al.png'
import SlideTitle from "../Components/SlideTitle.vue";
import SlideBody from "../Components/SlideBody.vue";
import Navigation from "../Components/Navigation.vue";

</script>
<template>

  <div class="flex flex-col h-full pt-8">

    <img :src="llamaImage" alt="Llama"
         class="w-1/2 object-contain border-2 border-white h-auto object-contain mx-auto"/>

    <div class="mt-8 text-center">
      <SlideTitle>SD PHPx Meetup 5/13</SlideTitle>

      <SlideBody>
        <p class="mb-4">Thanks for coming! If you want to follow along you can clone this repo: </p>
        <code class="bg-gray-800/20 p-4">https://github.com/nsbucky/prism-demo</code>
      </SlideBody>
    </div>

    <Navigation next="/slides/2"/>

  </div>
</template>
