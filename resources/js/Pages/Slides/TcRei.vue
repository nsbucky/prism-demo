<script setup>
</script>

<template>
    <BaseSlide>
        <template #title><span class="text-pink-400 font-bold">T</span>houghtfully
            <span class="text-pink-400 font-bold">C</span>reate
            <span class="text-pink-400 font-bold">R</span>eally
            <span class="text-pink-400 font-bold">E</span>xcellent
            <span class="text-pink-400 font-bold">I</span>nput</template>
        <template #content>
            <div class="flex flex-col">

                <p class="mb-8">This prompting framework helps improve the performance of language models by using reinforcement learning
                    techniques to fine-tune the model based on human feedback, enhancing the model's ability to generate
                    coherent and contextually relevant text.</p>

                <dl class="grid grid-cols-3 gap-4 min-w-md mx-auto">
                    <dt class="font-bold text-right col-span-1"><span class="text-pink-500">T</span>ask</dt>
                    <dd class="mb-4 col-span-2">What do you want it to do?</dd>
                    <dt class="font-bold text-right col-span-1"><span class="text-pink-500">C</span>ontext</dt>
                    <dd class="mb-4 col-span-2">Provide relevant information to understand the task.</dd>
                    <dt class="font-bold text-right col-span-1"><span class="text-pink-500">R</span>eferences</dt>
                    <dd class="mb-4 col-span-2">Examples of similar tasks to guide its response.</dd>
                    <dt class="font-bold text-right col-span-1"><span class="text-pink-500">E</span>valuate</dt>
                    <dd class="mb-4 col-span-2">Assess output to determine if it meets the desired criteria.</dd>
                    <dt class="font-bold text-right col-span-1"><span class="text-pink-500">I</span>terate</dt>
                    <dd class="mb-4 col-span-2">Make changes, try again!</dd>
                </dl>

            </div>
        </template>
    </BaseSlide>
</template>

<style scoped>
    dt {
        color: #f472b6; /* Tailwind pink-500 */
    }

</style>
