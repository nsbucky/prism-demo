<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        PrismServer::register(
            'spatuala-creator',
            fn () => Prism::text()
                ->using(Provider::Ollama, 'llama3.2')
                ->withSystemPrompt('You love Weird Al. Admit it.');
        );
    }
}`

</script>

<template>
    <BaseSlide next="/slides/11" previous="/slides/9">
        <template #title>Prism Server</template>
        <template #content>
            <p>Expose your Prism-powered AI models through a standardized API, making it a breeze to integrate with
                OpenAI-compatible APIs.</p>

            <h4 class="text-2xl text-pink-500 my-4">Simple Process</h4>
            <ol class="list-disc list-inside mb-4">
                <li>Enable Prism Server in <code>config/prism.php</code> (defaults to enabled)</li>
                <li>Register your Prism models</li>
                <li>Use OpenWeb UI or ChatGPT Web UIs to interface with your registered models.</li>
            </ol>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode />
        </template>
        <template #footer>
            <div class="flex flex-col w-1/2 mx-auto">
                <p class="text-center text-xl">
                    <a href="http://localhost:3000" target="_blank" class="text-orange-500 hover:underline">
                    Launch OpenWebUI
                    </a>
                </p>
                <code class="bg-gray-800/20 p-2 rounded-lg">
                    POST http://localhost/prism/openai/v1/chat/completions<br>
                    GET http://localhost/prism/openai/v1/models
                </code>
            </div>
        </template>
    </BaseSlide>
</template>
