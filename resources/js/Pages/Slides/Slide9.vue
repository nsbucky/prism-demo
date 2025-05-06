<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/10" previous="/slides/8">
        <template #title>NOT FINISHED</template>
        <template #content>
            content
        </template>
    </BaseSlide>
</template>
