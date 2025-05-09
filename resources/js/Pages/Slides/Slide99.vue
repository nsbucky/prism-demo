<script setup>
import {reactive, ref} from "vue";

const form = reactive({
    prompt: null
});
const response = ref("");
const loading = ref(false);

function submit() {
    loading.value = true;
    // Prevent form submission if the prompt is empty
    if (!form.prompt) {
        alert("Please enter a prompt.");
        return;
    }

    // Make an API call to submit the form
    axios.post("/song", {prompt: form.prompt})
        .then((res) => {
            response.value = res.data;
            console.log("Response:", response.value);
            loading.value = false;
        })
        .catch((error) => {
            console.error("Error:", error);
        });
}
</script>

<template>
    <BaseSlide next="/slides/10" previous="/slides/99">
        <template #title>🎼 🎵 🎶 Your Parody Song 🎵🎵 </template>
        <template #content>
          <div>
              <form @submit.prevent="submit">
                  <div class="flex flex-col items-center">

                      <input
                          v-model="form.prompt"
                          type="text"
                          id="prompt"
                          class="border border-gray-300 rounded-lg p-2 mb-4 w-full"
                          required
                      />

                      <button
                          type="submit"
                          class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200"
                      >
                          {{ loading ? 'Writing...' : 'Generate' }}
                      </button>
                      <div v-if="response" class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-88">
                          {{ response }}
                      </div>
                  </div>
              </form>
          </div>
        </template>
    </BaseSlide>
</template>
