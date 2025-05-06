<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/12" previous="/slides/10">
        <template #title>NOT FINISHED</template>
        <template #content>
            content
        </template>
    </BaseSlide>
</template>
