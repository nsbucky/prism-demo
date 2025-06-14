<script setup>
import {reactive, ref} from "vue";
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
  ][Math.floor(Math.random() * 10)],
  temperature: null,
  topP: null
});

function handleTemperatureChange() {
  if (streamForm.temperature !== null) streamForm.topP = null;
}

const streamingResponse = ref("");
const isStreaming = ref(false);

function streamResponse() {
  isStreaming.value = true;
  streamingResponse.value = "";

  // Build query parameters
  let queryParams = `prompt=${encodeURIComponent(streamForm.prompt)}`;
  if (streamForm.temperature !== null) {
    queryParams += `&temperature=${streamForm.temperature}`;
  }
  if (streamForm.topP !== null) {
    queryParams += `&topP=${streamForm.topP}`;
  }

  // Create EventSource for Server-Sent Events
  const eventSource = new EventSource(`/stream?${queryParams}`);

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
    <template #title>Chat Window</template>
    <template #content>

      <div>
        <h4 class="text-orange-300 font-bold mb-3">Streamed response</h4>
        <form @submit.prevent="streamResponse">
          <div class="flex items-center mb-3">
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

          <div class="flex gap-4 mb-3">
            <div class="flex items-center">
              <label for="temperature" class="mr-2 text-sm" :class="{ 'opacity-50': streamForm.topP !== null }">Temperature:</label>
              <select
                  v-model="streamForm.temperature"
                  id="temperature"
                  class="border border-gray-300 rounded-lg p-1 bg-gray-800/40 border-orange-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 text-sm"
                  :disabled="streamForm.topP !== null"
                  :class="{ 'opacity-50 cursor-not-allowed': streamForm.topP !== null }"
                  @change="handleTemperatureChange"
              >
                <option :value="null">Default</option>
                <option :value="0.0">0.0 (Deterministic)</option>
                <option :value="0.2">0.2</option>
                <option :value="0.5">0.5</option>
                <option :value="0.7">0.7</option>
                <option :value="0.8">0.8</option>
                <option :value="1.0">1.0 (Creative)</option>
              </select>
            </div>

            <div class="flex items-center">
              <label for="topP" class="mr-2 text-sm" :class="{ 'opacity-50': streamForm.temperature !== null }">Top
                P:</label>
              <select
                  v-model="streamForm.topP"
                  id="topP"
                  class="border border-gray-300 rounded-lg p-1 bg-gray-800/40 border-orange-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 text-sm"
                  :disabled="streamForm.temperature !== null"
                  :class="{ 'opacity-50 cursor-not-allowed': streamForm.temperature !== null }"
                  @change="() => { if (streamForm.topP !== null) streamForm.temperature = null }"
              >
                <option :value="null">Default</option>
                <option :value="0.1">0.1 (Focused)</option>
                <option :value="0.3">0.3</option>
                <option :value="0.5">0.5</option>
                <option :value="0.7">0.7</option>
                <option :value="0.9">0.9 (Diverse)</option>
                <option :value="1.0">1.0</option>
              </select>
            </div>

            <div class="flex items-center text-xs text-gray-400 italic">
              <span v-if="streamForm.temperature === null && streamForm.topP === null">(Choose one parameter)</span>
              <span v-else-if="streamForm.temperature !== null" class="text-orange-400">Using Temperature</span>
              <span v-else class="text-orange-400">Using Top P</span>
            </div>
          </div>

          <div v-if="streamingResponse"
               class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48 whitespace-pre-line">
            {{ streamingResponse }}
          </div>
        </form>
      </div>

    </template>

  </BaseSlide>
</template>

