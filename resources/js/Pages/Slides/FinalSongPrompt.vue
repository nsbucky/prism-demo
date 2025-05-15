<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `$promptView = view('lyrics', [
                'userPrompt' => $this->buildUserPrompt(),
                'keywords'   => $this->extractKeywords(),
                'document'   => $this->getMatchingLyric(),
            ]);

Prism::text()
 ->using(Provider::Ollama, 'llama3.2')
 ->withClientOptions(['timeout' => 120, 'usingTemperature' => 0.7])
 ->withPrompt($promptView)
 ->asText()`
</script>

<template>
    <BaseSlide>
        <template #title>Final Prompt - use Blade!</template>
        <template #content>

            <p>Prism allows you to use Laravel Blade to format your final prompt.</p>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />

            <h3>Guardrails</h3>
            <p>Need more security on your user prompts? Consider using some guardrails by
                running your propmpt through 1 more model. The Granite Guardian models are a robust suite of safeguards
                designed to detect risks in both prompts and responses, ensuring safe and responsible use with any large
                language model while promoting responsible AI development.</p>

            <p><a href="https://huggingface.co/ibm-granite/granite-guardian-3.0-8b">Granite Guardian</a>
                <a href="https://www.ibm.com/granite/docs/models/guardian/">Website</a>
            </p>
        </template>
    </BaseSlide>
</template>
