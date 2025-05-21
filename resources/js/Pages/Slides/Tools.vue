<script setup>

import {reactive, ref} from "vue";
import axios from "axios";

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
    <template #title>Tool Example</template>
    <template #content>
      <p>Can ollama find our user? Define your input parameters and use case.</p>

      <form @submit.prevent="submit">
        <div class="flex items-center">

          <input
              v-model="form.prompt"
              type="text"
              id="prompt"
              class="border border-gray-300 rounded-lg p-2 mr-4 w-full"
              placeholder="Who has the best tacos?"
              required
          />
          <button
              type="submit"
              class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200"
          >
            {{ normalResponseLoading ? 'Searching...' : 'Search' }}

          </button>

        </div>
      </form>

        <div v-if="response" class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48">
            {{ response }}
        </div>

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
