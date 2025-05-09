<script setup>

import PrismLogo from '@/assets/prism.png'
import TheMan from '@/assets/tjmiller.jpg'
</script>

<template>

    <BaseSlide next="/slides/6" previous="/slides/4">

        <template #title>use Prism\Prism\Prism;</template>

        <template #content>

            <div class="grid grid-cols-2 gap-4 p-4">
                <div class="text-center">
                    <img :src=PrismLogo alt="Prism Logo" class="bg-white rounded-lg shadow-lg p-4 mb-2 w-full"
                         width="573" height="294"/>
                    <a href="https://prismphp.com/" target="_blank" rel="noopener noreferrer">Prism PHP</a>
                    |
                    <a href="https://prismphp.com/getting-started/introduction.html/" target="_blank"
                       rel="noopener noreferrer">Docs</a>
                </div>
                <div class="text-center">
                    <img :src=TheMan alt="T.J. Miller" class="bg-white rounded-lg shadow-lg p-4 mb-2 mx-auto"
                         style="width:250px"/>
                    <a href="https://www.tjmiller.me/" target="_blank">T.J. Miller</a>
                </div>
            </div>

            <SlideBody>

                <p class="mb-2">
                    <b>Unified Provider Interface:</b> simple to switch between a variety of AI providers. 👍
                </p>

                <p class="mb-2"><b>Tool System:</b> write PHP code to define tools that can be used by the AI
                    Provider <span
                        class="text-pink-400">(scary!)</span>
                </p>

                <p class="mb-2"><b>Image &amp; Document Support:</b> Prism can handle this too.
                    <span class="text-orange-300">(neat!)</span>
                </p>

            </SlideBody>

        </template>

        <template #footer>
            <div class="flex flex-col w-1/2 mx-auto">
                <p class="text-center">Simple install</p>
                <code class="bg-gray-800/20 p-2 rounded-lg">
                    composer require prism-php/prism<br>
                    php artisan vendor:publish --tag=prism-config
                </code>
            </div>
        </template>
    </BaseSlide>

</template>
