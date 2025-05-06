<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/8" previous="/slides/6">
        <template #title>NOT FINISHED</template>
        <template #content>
            content
        </template>
    </BaseSlide>
</template>
