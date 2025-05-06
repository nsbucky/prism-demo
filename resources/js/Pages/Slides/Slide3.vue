<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/4" previous="/slides/2">
        <template #title>What are going to cover today?</template>
        <template #content>
            <h2>
            Foundation:
            </h2>

            Use Laravel sail to run our project.
            Using Vue + Inertia for the interface
            Mysql and Postgres integration
            Make install scripts?
            install deps and get it running using Sail
            Check to see all the models are running etc

            <h2>
                Coding:
            </h2>

            Talk to the Llama, get a response.
            <code>docker exec -it ollama:latest /bin/bash</code>
            Create a custom tool to use.
            Create a simple RAG implementation using Weird Al
        </template>
    </BaseSlide>
</template>


