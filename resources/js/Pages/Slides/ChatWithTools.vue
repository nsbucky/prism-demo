<script setup>
import {reactive, ref, onMounted} from "vue";
import 'deep-chat';
import axios from "axios";


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

const toolResults = ref([]);
const toolHtml = ref('');
const sessionId = ref(null);
const history = ref([]);


// Get CSRF token - ensure it exists
const getCsrfToken = () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) {
    console.error('CSRF token not found!');
  }
  return token || '';
};

// deep chat
const connectionParameters = {
  url: '/chat',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': getCsrfToken(),
  },
  method: 'POST',
  stream: true,
  requestBodyLimits: {
    maxMessages: -1
  }
};

onMounted(() => {
   fetchHistory();
});

console.log('CSRF Token:', getCsrfToken());
console.log('Connection Parameters:', connectionParameters);
// Request interceptor to format messages for the controller
const requestInterceptor = (body) => {
  // DeepChat sends messages in format: {messages: [{role: 'user', text: '...'}]}
  // Convert to controller's expected format
  return {
    messages: body.messages || [],
    temperature: streamForm.temperature,
    topP: streamForm.topP,
    session_id: sessionId.value,
    //_token: getCsrfToken() // Also include CSRF token in body
  };
};

const responseInterceptor = (response) => {
  if (response.toolResults) {
    console.log(response.toolResults);
    toolResults.value = response.toolResults;
  }

  if (response.toolHtml) {
    console.log(response.toolHtml);
    toolHtml.value = response.toolHtml;
  }

  if (response.session_id) {
    sessionId.value = response.session_id;
    console.log('Session ID:', response.session_id);
  }

  if (response.done) {
    //isStreaming.value = false;
  }

  return response;
};

// Fetch chat history from internal endpoint
async function fetchHistory() {

  try {
    const response = await axios.get(sessionId.value ? `/chat-history/${sessionId}` : '/chat-history');
    history.value = response.data || [];
    console.log('Chat history fetched:', response.data);
  } catch (error) {
    console.error('Failed to fetch chat history:', error);
  }
}

</script>

<template>
  <BaseSlide>
    <template #title>Chat Window</template>
    <template #content>

      <div>

        <deep-chat
            style="width: 500px;"
            class="w-full h-full"
            :connect="connectionParameters"
            :responseInterceptor="responseInterceptor"
            :history="history"
        />

        <h4 class="text-orange-300 font-bold mb-3">Options</h4>

        <form>
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

        </form>
        <div v-if="toolResults">
          <h4 class="text-orange-300 font-bold mt-4">Tool Results</h4>
          <div class="mt-2 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48 whitespace-pre-line">
            {{ toolResults }}
          </div>
        </div>
        <div v-if="toolHtml">
          <h4 class="text-orange-300 font-bold mt-4">Tool HTML</h4>
          <div class="mt-2 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48 whitespace-pre-line">
            {{ toolHtml }}
          </div>
        </div>
      </div>

    </template>

  </BaseSlide>
</template>

