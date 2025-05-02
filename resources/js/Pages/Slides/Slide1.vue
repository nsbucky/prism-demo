<script setup>
import llamaImage from '../../assets/llama-al.png'
</script>
<template>
    <div class="flex flex-row w-full h-full">
        <!-- Left side - 1/3 width -->
        <div class="w-1/3 p-4 flex items-center justify-center">
            <img :src="llamaImage" alt="Llama" class="w-full object-contain" />
        </div>

        <!-- Right side - 2/3 width -->
        <div class="w-2/3 p-8 flex flex-col justify-center">
            <h2 class="text-3xl font-bold mb-4">Hey there!</h2>
            <p class="text-lg">Your content goes here. This section takes up 2/3 of the container width.</p>
        </div>
    </div>
</template>
