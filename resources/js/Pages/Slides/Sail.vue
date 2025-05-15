<script setup>

</script>

<template>
    <BaseSlide>
        <template #title>Up &amp; Running With Sail</template>
        <template #content>

            <p>Laravel's Sail is a lightweight command-line interface that interacts with the
                Docker environment in this project.</p>

            <p>The composer.json has a setup script that will perform the following:.</p>

            <div class="bg-gray-800/20 text-white p-4 whitespace-pre-line rounded-lg font-mono mb-4 ">
                cp .env.example .env<br>
                composer install<br>
                ./vendor/bin/sail up -d<br>
                ./vendor/bin/sail npm install<br>
                ./vendor/bin/sail artisan key:generate<br>
                ./vendor/bin/sail artisan migrate<br>
                ./vendor/bin/sail artisan db:seed<br>
                ./vendor/bin/sail composer run dev
            </div>

            <div class="flex flex-row gap-8 mt-8">

                <div class="w-1/2"><h3 class="text-orange-300">Run this command from project root</h3>
                    <code class="bg-gray-800/20">composer run setup</code>
                </div>

                <div class="w-1/2">
                    <h3 class="text-orange-300">Done?</h3>
                    <code class="bg-gray-800/20">./vendor/bin/sail down</code>
                </div>
            </div>


        </template>
    </BaseSlide>
</template>

<style scoped>
p {
    margin-bottom: 1rem;
}

code {
    margin-bottom: 1rem;
    display: block;
    padding: 1rem;
}

</style>
