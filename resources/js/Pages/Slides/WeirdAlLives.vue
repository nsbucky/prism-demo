<script setup>
import {ref, onMounted, onUnmounted} from "vue";
import {useForm, router} from '@inertiajs/vue3';
import LoadingSpinner from '../Components/LoadingSpinner.vue';
import BaseSlide from '../Components/BaseSlide.vue';

const parodySongForm = useForm({
  prompt: ''
});

const isProcessing = ref(false);
const createdSong = ref(null);
let echo = null;

onMounted(() => {
  // Initialize Laravel Echo for Reverb
  if (window.Echo) {
    echo = window.Echo.private(`user.${window.Laravel?.user?.id || 'guest'}`)
      .listen('ParodySongCompleted', (e) => {
        console.log('Song creation completed:', e);
        isProcessing.value = false;
        createdSong.value = e.song;
      });
  }
});

onUnmounted(() => {
  if (echo) {
    echo.stopListening('ParodySongCompleted');
  }
});

function submitPrompt() {
  isProcessing.value = true;
  createdSong.value = null;
  
  router.post('/parody-song', {
    prompt: parodySongForm.prompt
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Job has been dispatched successfully
      console.log('Parody song job dispatched');
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
      isProcessing.value = false;
    },
  });
}
</script>

<template>
  <BaseSlide>
    <template #title>Weird Al Lives!</template>
    <template #content>
      <div class="space-y-6">
        <p class="text-lg">
          Let's create a parody song in the style of <span class="text-pink-400 font-bold">"Weird Al" Yankovic</span>! 
          Enter a prompt below and our AI will generate lyrics that would make Al proud.
        </p>

        <div class="bg-gray-800/40 rounded-lg p-6">
          <h4 class="text-orange-300 font-bold mb-4">Parody Song Generator</h4>
          <form @submit.prevent="submitPrompt">
            <div class="flex flex-col space-y-4">
              <input
                  v-model="parodySongForm.prompt"
                  type="text"
                  id="parody-prompt"
                  class="w-full border border-gray-300 rounded-lg p-3 bg-gray-800/40 border-orange-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  placeholder="Enter a topic for your parody song (e.g., 'Working from home', 'Social media addiction', 'Coffee obsession')..."
                  required
                  :disabled="isProcessing"
              />
              <button
                  type="submit"
                  class="bg-purple-600 text-white rounded-lg px-6 py-3 hover:bg-purple-700 transition duration-200 flex items-center justify-center gap-2 font-semibold"
                  :disabled="parodySongForm.processing || isProcessing"
              >
                <LoadingSpinner v-if="parodySongForm.processing || isProcessing" size="20"/>
                <span>{{ isProcessing ? 'Creating Your Parody...' : 'Generate Parody Song' }}</span>
              </button>
            </div>
          </form>
        </div>

        <div v-if="isProcessing && !createdSong" class="bg-yellow-900/30 border border-yellow-600 rounded-lg p-4">
          <p class="text-yellow-200 flex items-center gap-2">
            <LoadingSpinner size="16"/>
            <span>Your parody is being crafted... This usually takes about 30 seconds. Channel your inner Weird Al!</span>
          </p>
        </div>

        <div v-if="createdSong" class="bg-green-900/30 border border-green-600 rounded-lg p-6 space-y-4">
          <h3 class="text-green-300 font-bold text-xl">{{ createdSong.title }}</h3>
          <div class="text-gray-300">
            <p class="text-sm text-gray-400 mb-2">Artist: {{ createdSong.artist }}</p>
            <div class="whitespace-pre-wrap font-mono text-sm bg-black/30 p-4 rounded">{{ createdSong.lyrics }}</div>
          </div>
          <div v-if="createdSong.style" class="text-sm text-gray-400">
            <span class="font-semibold">Style:</span> {{ createdSong.style }}
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex flex-col items-center space-y-2">
        <p class="text-sm text-gray-400">Powered by AI + Weird Al's Spirit</p>
        <code class="bg-gray-800/20 p-2 rounded-lg text-xs">
          OllamaRhymesWeirdlyCommand + Laravel Reverb
        </code>
      </div>
    </template>
  </BaseSlide>
</template>

<style scoped>
</style>