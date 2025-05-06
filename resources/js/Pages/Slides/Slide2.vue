<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
import KbSad from "@/assets/kbsad.jpg";
import Sedona from "@/assets/sedona.jpg";
import BandDudes from "@/assets/band-dudes.jpg";
</script>

<template>
    <BaseSlide next="/slides/3" previous="/slides/1">
        <template #title>
            Who Am I?
        </template>

        <template #content>
            <div class="flex flex-row justify-between space-x-4">

                <div class="text-center">
                    <img :src=KbSad alt="Developing is hard"
                         class="shadow-lg border-2 border-white rounded-sm min-h-[400px] object-cover mb-2"/>
                    <p>PHP since 2001.<br><code>@thebusypixel</code></p>
                </div>
                <div class="text-center">
                    <img :src=Sedona alt="Sedona bike trip"
                         class="shadow-lg border-2 border-white rounded-sm min-h-[400px] object-cover mb-2">
                    <p>Lots of mountain biking.</p>
                </div>
                <div class="text-center">
                    <img :src=BandDudes alt="Band Dudes"
                         class="shadow-lg border-2 border-white rounded-sm min-h-[400px] object-cover mb-2">
                    <p>Enjoy playing guitar, bass, and drums.
                        <br><span class="text-sm">
                        (So many programmers are musicians it turns out!)
                        </span>
                    </p>
                </div>
            </div>

        </template>
    </BaseSlide>


</template>
