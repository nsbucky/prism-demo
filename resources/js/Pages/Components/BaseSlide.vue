<script setup>

import SlideTitle from "./SlideTitle.vue";
import SlideBody from "./SlideBody.vue";
import Navigation from "./Navigation.vue";

defineProps({
    next: {
        type: String,
        default: null
    },
    previous: {
        type: String,
        default: null
    }
})

</script>
<template>
    <div class="flex flex-col h-full text-white bg-gray-800/20 p-10 justify-center">

        <SlideTitle class="text-center mb-10">
            <slot name="title"></slot>
        </SlideTitle>

        <SlideBody class="mx-auto w-2/3">
            <slot name="content"></slot>
        </SlideBody>

        <div class="flex flex-row justify-between mt-10">
            <slot name="footer"></slot>
        </div>

    </div>

    <Navigation :next=next :previous=previous />
</template>
