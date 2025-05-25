<script setup>

import {reactive, ref} from "vue";
import axios from "axios";
import LoadingSpinner from '../Components/LoadingSpinner.vue';

const form = reactive({
  prompt: 'Can you search for a user named Weird Al? How many users are there?',
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
      <p class="text-center">Can ollama find our user? Multiple tools can be made available and the LLM will decide when
      to use the appropriate one.</p>

      <form @submit.prevent="submit" class="mt-8">
        <div class="flex items-center">

          <input
              v-model="form.prompt"
              type="text"
              id="prompt"
              class="border border-gray-300 rounded-lg p-2 mr-4 w-full"
              required
          />
          <button
              type="submit"
              class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200 flex items-center gap-2"
              :disabled="normalResponseLoading"
          >
            <LoadingSpinner v-if="normalResponseLoading" size="16" />
            <span>{{ normalResponseLoading ? 'Searching...' : 'Search' }}</span>

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
          ollama:tool<br>
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
