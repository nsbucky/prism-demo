<script setup>

import llamaImage from '../../assets/llama-al.png'
import SlideTitle from "../Components/SlideTitle.vue";
import SlideBody from "../Components/SlideBody.vue";
import Navigation from "../Components/Navigation.vue";
import Qr from '@/assets/bitly-qr.png'

</script>
<template>

    <div class="flex flex-col h-full pt-8">

        <div style="width: 80%" class="mx-auto">

            <img :src="llamaImage" alt="Llama"
                 class="w-1/2 object-contain border-2 border-white h-auto object-contain mx-auto"/>
        </div>

        <div class="mt-8">
            <SlideTitle class="text-center">PHP Tek 2025</SlideTitle>

            <SlideBody>
                <div class="flex justify-center mb-6 gap-4">
                    <div>
                        <img :src="Qr" alt="Github Repo" class="bg-white rounded-sm shadow-lg p-1 inline-block"
                             width="200"/>
                    </div>
                    <div>

                        <p class="mb-4">Thanks for coming! If you want to follow along you can clone this repo: </p>
                        <code class="bg-gray-800/20 p-4">https://github.com/nsbucky/prism-demo</code>

                    </div>
                </div>
            </SlideBody>

        </div>

        <Navigation/>

    </div>
</template>
