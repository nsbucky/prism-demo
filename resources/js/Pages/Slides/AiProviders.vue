<script setup>
import VueCodeBlock from '@wdns/vue-code-block';
import PrismProviders from "@/assets/providers.png";
const sampleCode = `Prism::text()->using(Provider::Ollama, 'llama3.2')`
</script>

<template>
    <BaseSlide>
        <template #title>Prism AI Provider Support</template>
        <template #content>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />

            <img :src="PrismProviders" class="mx-auto mb-4 border rounded-lg" alt="Prism Providers">

        </template>
    </BaseSlide>
</template>
