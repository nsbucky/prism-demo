<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/10" previous="/slides/8">
        <template #title>Weird AI</template>
        <template #content>
            I decided to use Weird Al lyrics as my context for a RAG implementation. expand on that.
        </template>
    </BaseSlide>
</template>
