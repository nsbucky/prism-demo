<script setup>
import { onMounted } from "vue"
import { Link, usePage } from '@inertiajs/vue3'
import { useSlides } from "../Stores/slides.js"

const { next, previous, setCurrentIndex } = useSlides()
const currentRoute = usePage().url

onMounted(() => {
    const currentSlideName = currentRoute.split('/').pop()
    setCurrentIndex(`/slides/${currentSlideName}`)
})
</script>

<template>
    <div class="flex flex-col text-white bg-gray-800/20 mt-auto">
        <nav class="bg-gray-800 text-white p-4">
            <ul class="flex space-x-4 items-center space-x-32 justify-center">
                <li v-if="previous">
                    <Link :href="previous" class="hover:text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Previous
                    </Link>
                </li>
                <li v-if="next">
                    <Link :href="next" class="hover:text-red-500">
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>
