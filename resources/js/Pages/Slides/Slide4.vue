<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/5" previous="/slides/3">
        <template #title>Laravel</template>
        <template #content>
            <p>Laravel is a web application framework with expressive, elegant syntax.</p>
            <p>We believe development must be an enjoyable and creative experience to be truly fulfilling.</p>
            <p>Laravel strives to be a framework for web artisans.</p>
        </template>
    </BaseSlide>
</template>


