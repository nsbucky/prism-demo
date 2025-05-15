<script setup>

import llamaImage from '../../assets/llama-al.png'
import SlideTitle from "../Components/SlideTitle.vue";
import SlideBody from "../Components/SlideBody.vue";
import Navigation from "../Components/Navigation.vue";

</script>
<template>

    <div class="flex flex-col h-full pt-8">

        <div style="width: 80%" class="mx-auto">

            <img :src="llamaImage" alt="Llama"
                 class="w-1/2 object-contain border-2 border-white h-auto object-contain mx-auto"/>
        </div>

        <div class="mt-8 text-center">
            <SlideTitle>PHP Tek 2025</SlideTitle>

            <SlideBody>
                <p class="mb-4">Thanks for coming! If you want to follow along you can clone this repo: </p>
                <code class="bg-gray-800/20 p-4">https://github.com/nsbucky/prism-demo</code>
                <p class="text-xs text-gray-500 p-4">
                    Llama in part by <a
                    href="https://unsplash.com/@lipnorth?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash">Greg
                    Lippert</a> on <a
                    href="https://unsplash.com/photos/white-camel-Z5Yha3jIHnc?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash">Unsplash</a>
                </p>
            </SlideBody>

        </div>

        <Navigation/>

    </div>
</template>
