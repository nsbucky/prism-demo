<script setup>

import {defineComponent} from "vue";
import SlideTitle from "../Components/SlideTitle.vue";
import SlideBody from "../Components/SlideBody.vue";

</script>

<template>
    <div class="flex flex-col items-center justify-center h-full">
        <SlideTitle>Kenrick aka thebusypixel</SlideTitle>
        <SlideBody>
            <p>A little about me:</p>
            <ul>
                <li>Been developing with PHP since 2001</li>
                <li>Love to make music</li>
                <li>Love to ride a moutain bike around San Diego</li>
            </ul>
        </SlideBody>
    </div>
</template>
