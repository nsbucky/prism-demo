<script setup>
import {ref, markRaw} from 'vue'
import {VueperSlides, VueperSlide} from 'vueperslides'
import 'vueperslides/dist/vueperslides.css'
import Slide2 from './Slide2.vue'
import BaseSlide from "../Components/BaseSlide.vue";

const slides = ref([
    {
        title: 'Prism',
        content: 'stuff about prism'
    }
])
</script>

<template>

    <BaseSlide next="/slides/6" previous="/slides/4">

    <vueper-slides progress  arrows-outside bullets-outside>
        <vueper-slide
            v-for="(slide, i) in slides"
            :key="i"
            :title="slide.title">
            <template v-slot:content>
                <component
                    v-if="slide.component"
                    :is="slide.component"
                />
                <template v-else>
                    {{ slide.content }}
                </template>
            </template>
        </vueper-slide>
    </vueper-slides>
    </BaseSlide>
</template>
