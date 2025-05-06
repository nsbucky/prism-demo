<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/12" previous="/slides/10">
        <template #title>Fin!</template>
        <template #content>
            Questions, comments, QR code (one of those rad ones, maybe using the slide image?) with links to the repo and stuff
        </template>
    </BaseSlide>
</template>
