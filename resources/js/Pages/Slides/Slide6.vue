<script setup>
import {reactive, ref} from "vue";
import axios from "axios";
import LoadingSpinner from '../Components/LoadingSpinner.vue';

const streamForm = reactive({
  prompt: [
    "If cats could code, what would their first app do?",
    "Why do ducks always look so suspicious in parks?",
    "Explain quantum physics using only pizza toppings.",
    "Would a giraffe enjoy roller skating? Why or why not?",
    "If you could only speak in rhymes, how would you order coffee?",
    "Describe a superhero whose only power is making toast.",
    "What happens if you microwave a cloud?",
    "Invent a new holiday for robots and describe the traditions.",
    "If socks could talk, what secrets would they reveal?",
    "Write a haiku about a confused potato."
  ][Math.floor(Math.random() * 10)]
});

const streamingResponse = ref("");
const isStreaming = ref(false);

function streamResponse() {
  isStreaming.value = true;
  streamingResponse.value = "";

  // Create EventSource for Server-Sent Events
  const eventSource = new EventSource(`/stream?prompt=${encodeURIComponent(streamForm.prompt)}`);

  eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);

    if (data.chunk) {
      streamingResponse.value += data.chunk;
    }

    if (data.done) {
      eventSource.close();
      isStreaming.value = false;
    }
  };

  eventSource.onerror = () => {
    eventSource.close();
    isStreaming.value = false;
  };
}
</script>

<template>
  <BaseSlide>
    <template #title>Talking to the Ollama</template>
    <template #content>
      <p class="mb-8">Prism does all the heavy lifting making it easy to send prompts to the LLM.
        Tune your prompts at runtime by changing <span class="text-pink-400">temperature</span> (randomness)
        or <span class="text-pink-400">topP</span> (output diversity).</p>

      <div class="flex mb-8">
        <div class="w-1/2">
          <h4>Temperature</h4>
          <p class="mb-4 text-sm">Controls the randomness of the model's output. Higher values (e.g., 0.8)
            make the output more random, while lower values (e.g., 0.2) make it more focused and
            deterministic.</p>
        </div>
        <div class="w-1/2">
          <h4>Top P</h4>
          <p class="mb-4 text-sm">
            Sets a probability threshold for word choices. A Top-P of 0.9 means the model will only pick
            from words that together make up the top 90% most likely options. Higher values (like 0.9) allow
            more creative responses, while lower values (like 0.3) make responses more focused and
            predictable.
          </p>
        </div>
      </div>
      <div>
        <h4 class="text-orange-300 font-bold mb-3">Streamed response</h4>
        <form @submit.prevent="streamResponse">
          <div class="flex items-center">
            <input
                v-model="streamForm.prompt"
                type="text"
                id="stream-prompt"
                class="w-200 mr-1 border border-gray-300 rounded-lg p-2 bg-gray-800/40 border-orange-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                required
            />
            <button
                type="submit"
                class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200 flex items-center gap-2"
                :disabled="isStreaming"
            >
              <LoadingSpinner v-if="isStreaming" size="16"/>
              <span>{{ isStreaming ? 'Working...' : 'Ask' }}</span>
            </button>

          </div>
          <div v-if="streamingResponse"
               class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48">
            {{ streamingResponse }}
          </div>
        </form>
      </div>

    </template>

    <template #footer>
      <div class="flex flex-col w-1/2 mx-auto">
        <h4>Laravel CLI Command</h4>
        <code class="bg-gray-800/20 p-2 rounded-lg">
          ./vendor/bin/sail artisan ollama:responds
        </code>
      </div>
    </template>

  </BaseSlide>
</template>

