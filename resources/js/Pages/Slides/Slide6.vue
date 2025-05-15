<script setup>
import {reactive, ref} from "vue";

import axios from "axios";

const form = reactive({
    prompt: "Where exactly is Spatula City?"
});

const streamForm = reactive({
    prompt: "Is it wise to 'just eat it' like Weird Al?"
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
                <div  class="w-1/2">
                    <h4>Top P</h4>
                    <p class="mb-4 text-sm">
                        Sets a probability threshold for word choices. A Top-P of 0.9 means the model will only pick
                        from words that together make up the top 90% most likely options. Higher values (like 0.9) allow
                        more creative responses, while lower values (like 0.3) make responses more focused and
                        predictable.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h4 class="text-orange-300 font-bold mb-3">Standard Response</h4>
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
                                {{ normalResponseLoading ? 'Waiting...' : 'Normal Response' }}
                            </button>
                            <div v-if="response" class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48">
                                {{ response }}
                            </div>
                        </div>
                    </form>
                </div>
                <div>

                    <h4 class="text-orange-300 font-bold mb-3">Streamed response</h4>
                    <form @submit.prevent="streamResponse">
                        <div class="flex flex-col items-center">
                            <input
                                v-model="streamForm.prompt"
                                type="text"
                                id="stream-prompt"
                                class="border border-gray-300 rounded-lg p-2 mb-4 w-full"
                                required
                            />
                            <button
                                type="submit"
                                class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200"
                                :disabled="isStreaming"
                            >
                                {{ isStreaming ? 'Streaming...' : 'Streamed Response' }}
                            </button>

                            <div v-if="streamingResponse"
                                 class="mt-4 p-4 bg-gray-800/20 rounded-lg w-full overflow-y-auto h-48">
                                {{ streamingResponse }}
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </template>

        <template #footer>
            <div class="flex flex-col w-1/2 mx-auto">
                <h4>Laravel CLI Command</h4>
                <code class="bg-gray-800/20 p-2 rounded-lg">
                    ./vendor/bin/sail artisan ollama:responds "Weird Al says I should 'Burn your candle at both ends,
                    Look a gift horse in the mouth, Mashed potatoes can be your friends'"
                </code>
            </div>
        </template>

    </BaseSlide>
</template>

