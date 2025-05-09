<script setup>
import Twitter from '@/assets/twitter-logo-2.svg'
import Discord from '@/assets/discord.svg'
import El from '@/assets/IMG_3565.jpg'
import Github from '@/assets/github-mark.svg'
</script>

<template>
    <BaseSlide next="/slides/12" previous="/slides/10">
        <template #title>🎆 Thanks for being here! 🎇</template>
        <template #content>

            <div class="text-center mb-6">
                <span class="text-3xl">✨ 🎉 🎊 🚀 🏆 👏 🙌 ✨</span>
            </div>

            <h3 class="text-3xl text-orange-300 text-center my-10">Questions, comments, suggestions?</h3>

            <div class="flex justify-center space-x-4 mt-8">

                <div>
                    <img :src=El alt="TBP home" class="bg-white rounded-sm shadow-lg p-1 inline-block" width="50"/>
                    thebusypixel.com
                </div>

                <div>
                    <a href="https://github.com/nsbucky">
                        <img :src=Github alt="Github" class="bg-white rounded-sm shadow-lg p-1 inline-block"
                             width="50"/>
                        @nsbucky
                    </a>
                </div>

                <div>
                    <img :src=Twitter alt="twitter" class="bg-white rounded-sm shadow-lg p-1 inline-block" width="50"/>
                    @thebusypixel
                </div>

                <div>
                    <img :src=Discord alt="Discord" class="bg-pink rounded-sm shadow-lg p-1 inline-block" width="50"/>
                    nsbucky
                </div>
            </div>

        </template>
    </BaseSlide>
</template>
