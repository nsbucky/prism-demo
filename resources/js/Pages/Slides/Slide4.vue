<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/5" previous="/slides/3">
        <template #title>Laravel Framework</template>
        <template #content>
            <div class="flex flex-row">

                <div class="text-center">
                    <img crossorigin="anonymous"
                         src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Laravel.svg/800px-Laravel.svg.png"
                         class="bg-white rounded-lg shadow-lg p-4 mb-2" height="500" alt="Laravel Logo" />
                    <a href="https://laravel.com" class="text-red">laravel.com</a>
                </div>

                <div class="ml-4 space-y-4">
                    <p>Laravel is a robust, progressive, PHP framework renowned for its elegant syntax, developer-friendly tools, and
                        extensive ecosystem. It excels in making web development faster and more enjoyable for PHP developers.
                        It is currently on version 12 and supports PHP 8.2 and above.</p>

                    <p>The framework provides a rich set of features like routing, templating, database migrations, and security
                        tools out-of-the-box, streamlining common tasks and fostering rapid application development. Its
                        strong community support and comprehensive documentation further empower PHP developers to build
                        sophisticated and scalable web applications efficiently.</p>
                </div>
            </div>
        </template>
    </BaseSlide>
</template>


