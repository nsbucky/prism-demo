<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `Prism::text()
 ->using(Provider::Ollama, 'llama3.2')
 ->withPrompt(view('lyrics', [
                'userPrompt' => $this->buildUserPrompt(),
                'keywords'   => $this->extractKeywords(),
                'document'   => $this->getMatchingLyric(),
            ]))
 ->asText()`
</script>

<template>
    <BaseSlide>
        <template #title>Final Prompt - <span class="text-pink-200">use Blade!</span></template>
        <template #content>

            <p>Prism allows you to use Laravel Blade to format your final prompt.</p>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />

            <h3 class="text-orange-500 my-3 text-3xl font-bold mt-4">Prompt Guardrails</h3>
            <p>Need more security on your user prompts? Consider using some guardrails by
                running your propmpt through 1 more model. The Granite Guardian models are a robust suite of safeguards
                designed to detect risks in both prompts and responses, ensuring safe and responsible use with any large
                language model while promoting responsible AI development.</p>

            <p class="text-center">🔗
            <a href="https://huggingface.co/ibm-granite/granite-guardian-3.0-8b" class="text-blue-300">Granite Guardian</a>
                 | 🔗
                <a href="https://www.ibm.com/granite/docs/models/guardian/"  class="text-blue-300">Website</a>
            </p>
        </template>
    </BaseSlide>
</template>
