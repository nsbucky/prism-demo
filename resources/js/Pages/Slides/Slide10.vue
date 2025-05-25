<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        PrismServer::register(
            'spatula-expert',
            fn () => Prism::text()
                ->using(Provider::Ollama, 'llama3.2')
                ->withSystemPrompt('You are a spatula expert.');
        );
    }
}`

</script>

<template>
    <BaseSlide>
        <template #title>Prism Server</template>
        <template #content>
            <p class="mb-6">Expose your Prism-powered AI models through a standardized API, making it a breeze to integrate with
                OpenAI-compatible APIs.</p>

            <ol class="list-disc list-inside mb-4 space-y-2">
                <li>Enable Prism Server in <code>config/prism.php</code> (defaults to enabled)</li>
                <li>Register your Prism models</li>
                <li>Use 🔗 <a href="http://localhost:3000" target="_blank" class="text-orange-500 hover:underline"> OpenWeb UI</a>
                    or ChatGPT Web UIs to interface with your registered models.</li>
            </ol>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />
        </template>
    </BaseSlide>
</template>
