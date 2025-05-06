<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
import VueCodeBlock from '@wdns/vue-code-block';
const sampleCode =`use Prism\\Prism\\Prism;
use Prism\\Prism\\Enums\\Provider;
use Prism\\Prism\\Facades\\Tool;

$tacoTownTool = Tool::as('tacos')
                    ->for('Get best place for tacos in town')
                    ->withStringParameter('city', 'The city to get tacos in')
                    ->using(function (string $city): string {
                    // Your weather API logic here
                    return "The tacos in {$city} at Albertacos are amazing!";
                });

$response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2')
            ->withMaxSteps(2)
            ->withPrompt('What city has the best tacos?')
            ->withTools([$tacoTownTool])
            ->asText();`
</script>

<template>
    <BaseSlide next="/slides/8" previous="/slides/6">
        <template #title>Getting Ollama To Use Tools</template>
        <template #content>
            <p>Need your AI assistant to check the weather, search a database, or call your API? Tools are here to help!
                They let you extend your AI's capabilities by giving it access to specific functions it can call.
            </p>
            <p>Think of tools as special functions that your AI assistant can use when it needs to perform specific
                tasks. Just like how Laravel's facades provide a clean interface to complex functionality, Prism tools
                give your AI a clean way to interact with external services and data sources.</p>

            <p>*You should use a higher number of max steps if you expect your initial prompt to make multiple tool calls.</p>

            <VueCodeBlock highlightjs lang="php" :code=sampleCode>
            </VueCodeBlock>
        </template>
    </BaseSlide>
</template>
