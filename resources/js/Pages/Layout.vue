<template>
    <main class="container mx-auto h-screen flex flex-col pb-20">
        <header class="bg-gray-800">
            <h1 class="text-3xl text-red-500 text-center font-bold p-3"><a href="/">Prismatic Ollama</a></h1>
        </header>
        <article class="flex-1 text-white bg-gray-800/30">
            <slot></slot>
        </article>
    </main>
</template>
