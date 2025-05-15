<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `Prism::text()
 ->using(Provider::Ollama, 'llama3.2')
 ->withClientOptions(['timeout' => 120])
 ->withPrompt(view('keywords', ['userPrompt' => $this->userPrompt]))
 ->asText();`

</script>

<template>
    <BaseSlide>
        <template #title>Extract Keywords</template>
        <template #content>

            <p class="mb-2 text-center">Include some keywords to help give the prompt more context</p>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />

        </template>
    </BaseSlide>
</template>
