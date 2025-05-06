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
    <div class="text-white bg-gray-800/20 p-10 justify-center">

        <SlideTitle class="text-center mb-6">
            <slot name="title"></slot>
        </SlideTitle>

        <SlideBody class="mx-auto w-2/3">
            <slot name="content"></slot>
        </SlideBody>

    </div>

    <Navigation :next=next :previous=previous />
</template>
