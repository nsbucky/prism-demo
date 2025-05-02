<script setup>

import llamaImage from '../../assets/llama-al.png'
import SlideTitle from "../Components/SlideTitle.vue";
import SlideBody from "../Components/SlideBody.vue";

</script>
<template>
    <div class="flex flex-row w-full h-full text-white bg-gray-800/20 ">

        <!-- Left side - 1/3 width -->
        <div class="w-1/3 p-4 flex items-center justify-center">
            <img :src="llamaImage" alt="Llama" class="w-full object-contain border-2 border-white w-full h-auto object-contain" />
        </div>

        <!-- Right side - 2/3 width -->
        <div class="w-2/3 p-8 flex flex-col justify-center">
            <SlideTitle>SDPHP Meetup 5/13</SlideTitle>

            <SlideBody>
                <p>Thanks for coming! If you want to follow along you can clone this repo: </p>
                <code>https://github.com/nsbucky/prism-demo</code>
            </SlideBody>
        </div>
    </div>
</template>
