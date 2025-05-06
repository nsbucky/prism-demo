<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/9" previous="/slides/7">
        <template #title>NOT FINISHED</template>
        <template #content>
            content
        </template>
    </BaseSlide>
</template>
