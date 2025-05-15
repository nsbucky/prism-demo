<script setup>
import {reactive, ref} from "vue";

import axios from "axios";

const form = reactive({
  prompt: null
});

const streamForm = reactive({
  prompt: null
});

const response = ref("");
const normalResponseLoading = ref(false);

const streamingResponse = ref("");
const isStreaming = ref(false);

function submit() {
  normalResponseLoading.value = true;
  // Prevent form submission if the prompt is empty
  if (!form.prompt) {
    alert("Please enter a prompt.");
    return;
  }

  // Make an API call to submit the form
  axios.post("/responds", {prompt: form.prompt})
      .then((res) => {
        response.value = res.data;
        console.log("Response:", response.value);
        normalResponseLoading.value = false;
      })
      .catch((error) => {
        console.error("Error:", error);
      });
}

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
      <p>Prism does all the heavy lifting so that you can easily pass in text as your prompt.</p>
      <p>You can also pretend this happens easily via a test.</p>

      <hr class="my-8"/>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <h4>Standard Response</h4>
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
                {{ normalResponseLoading ? 'Waiting...' : 'Normal Response' }}
              </button>
              <div v-if="response" class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48">
                {{ response }}
              </div>
            </div>
          </form>
        </div>
        <div>

          <h4>Streamed response</h4>
          <form @submit.prevent="streamResponse">
            <div class="flex flex-col items-center">
              <input
                  v-model="streamForm.prompt"
                  type="text"
                  id="stream-prompt"
                  class="border border-gray-300 rounded-lg p-2 mb-4 w-full"
                  placeholder="How do you write a song?"
                  required
              />
              <button
                  type="submit"
                  class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200"
                  :disabled="isStreaming"
              >
                {{ isStreaming ? 'Streaming...' : 'Streamed Response' }}
              </button>

              <div v-if="streamingResponse" class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48">
                {{ streamingResponse }}
              </div>
            </div>
          </form>
        </div>
      </div>


      <hr class="my-8"/>


    </template>
    <template #footer>
      <div class="flex flex-col w-1/2 mx-auto">
        <code class="bg-gray-800/20 p-2 rounded-lg">
          docker exec -it ollama /bin/bash
          ollama list
          <br>
          php artisan ollama:responds {prompt}
        </code>
      </div>
    </template>
  </BaseSlide>
</template>
