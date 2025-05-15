<script setup>

import VueCodeBlock from '@wdns/vue-code-block';
import {reactive, ref} from "vue";
import axios from "axios";

const sampleCode = `$tacoTownTool = Tool::as('tacos')
                    ->for('Get best place for tacos in town')
                    ->withStringParameter('city', 'The city to get tacos in')
                    ->using(function (string $city): string {
                    return "The tacos in {$city} at Albertacos are amazing!";
                });

$response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2')
            ->withMaxSteps(2)
            ->withPrompt('What city has the best tacos?')
            ->withTools([$tacoTownTool])
            ->asText();`

const form = reactive({
  prompt: 'Can you search for a user named Test User?'
});

const response = ref("");
const normalResponseLoading = ref(false);

function submit() {
  normalResponseLoading.value = true;
  // Prevent form submission if the prompt is empty
  if (!form.prompt) {
    alert("Please enter a prompt.");
    return;
  }

  // Make an API call to submit the form
  axios.post("/tool", {prompt: form.prompt})
      .then((res) => {
        response.value = res.data;
        console.log("Response:", response.value);
        normalResponseLoading.value = false;
      })
      .catch((error) => {
        console.error("Error:", error);
      });
}
</script>

<template>
  <BaseSlide>
    <template #title>Using Prism to provide tools for Ollama</template>
    <template #content>
      <p>Extend the ability of your prompts by using tools. Prism provides a clean, Laravel friendly interface to enable tools in your prompts. </p>

      <p>These are named tools that can be used by the AI, if it determines that it needs to.
        Not every model supports tools, or even handles them the same way.</p>


      <VueCodeBlock highlightjs lang="php" :code=sampleCode />

      <form @submit.prevent="submit">
        <div class="flex flex-col items-center">

          <input
              v-model="form.prompt"
              type="text"
              id="prompt"
              class="border border-gray-300 rounded-lg p-2 mb-4 w-full"
              placeholder="Who has the best tacos?"
              required
          />
          <button
              type="submit"
              class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200"
          >
            {{ normalResponseLoading ? 'Searching...' : 'Search' }}

          </button>
          <div v-if="response" class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48">
            {{ response }}
          </div>
        </div>
      </form>

    </template>
    <template #footer>
      <div class="flex flex-col w-1/2 mx-auto">
        <code class="bg-gray-800/20 p-2 rounded-lg">
          ollama:tool "Test User"<br>
          ollama:tool-users
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
