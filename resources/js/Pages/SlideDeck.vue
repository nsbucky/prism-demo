<script setup>
import {ref, markRaw} from 'vue'
import {VueperSlides, VueperSlide} from 'vueperslides'
import 'vueperslides/dist/vueperslides.css'
import Layout from "./Layout.vue";
import Slide1 from './Slides/Slide1.vue'
import Slide2 from './Slides/Slide2.vue'

const slides = ref([
  {
    title: 'Slide #1',
    component: markRaw(Slide1)
  },
  {
    title: 'Slide #2',
    component: markRaw(Slide2) // More efficient
  }
])
</script>

<template>
  <Layout>
    <vueper-slides progress fixed-height="1000px" arrows-outside bullets-outside>
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
  </Layout>
</template>
