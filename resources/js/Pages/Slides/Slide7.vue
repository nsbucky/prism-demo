<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `use Prism\\Prism\\Prism;
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
    <template #title>Using Prism to provide tools for Ollama</template>
    <template #content>
      <p>Extend the ability of your prompts by using tools.</p>

      <p>Prism provides a clean, Laravel friendly interface to enable tools in your prompts. </p>

      <p>These are named tools that can be used by the AI, if it determines that it needs to.
        Not every model supports tools, or even handles them the same way.</p>


      <VueCodeBlock highlightjs lang="php" :code=sampleCode>
      </VueCodeBlock>
    </template>
    <template #footer>
      <div class="flex flex-col w-1/2 mx-auto">
        <code class="bg-gray-800/20 p-2 rounded-lg">
          ollama:tool "Test User"
          ollama:lyric "Amish Paradise"
        </code>
      </div>
    </template>
  </BaseSlide>
</template>
<style scoped>
  p {
    margin-bottom: 1rem;
  }
  </style>
