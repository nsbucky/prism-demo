<script setup>

import VueCodeBlock from '@wdns/vue-code-block';
import {reactive, ref} from "vue";
import axios from "axios";

const sampleCode = `$tool = Tool::as('spatula')
->for('Find the best place for spatulas in town')
->withStringParameter('city', 'The city that might have spatulas')
->using(fn (string $city): string => "{$city} has the best!");

$response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2')
            ->withMaxSteps(2)
            ->withPrompt('What city has the best spatulas?')
            ->withTools([$tool])
            ->withToolChoice('spatula')
            ->asText();`

</script>

<template>
  <BaseSlide>
    <template #title>Using Prism to provide tools for Ollama</template>
    <template #content>
      <p>Extend the ability of your prompts by using tools. Prism provides a clean, Laravel friendly interface to enable tools in your prompts. </p>

      <p>These are named tools that can be used by the AI, if it determines that it needs to.
        Not every model supports tools, or even handles them the same way.</p>


      <VueCodeBlock highlightjs lang="php" :code=sampleCode />

    </template>
  </BaseSlide>
</template>
<style scoped>
  p {
    margin-bottom: 1rem;
  }
  </style>
