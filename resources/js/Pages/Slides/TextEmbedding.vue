<script setup>
import {computed, ref} from "vue";
import {useForm, usePage, router} from '@inertiajs/vue3';
import VueCodeBlock from '@wdns/vue-code-block';
import LoadingSpinner from '../Components/LoadingSpinner.vue';

const sampleCode = `Prism::embeddings()
    ->using(Provider::Ollama, 'mxbai-embed-large')
    ->fromInput('Welcome to Spatula City! Where are we?')
    ->asEmbeddings();`

const embeddingForm = useForm({
  text: 'Welcome to Spatula City! Where are we?'
});

const page = usePage();
const embeddingResponse = computed(() => page.props.flash?.payload);

function getEmbedding() {
  router.post('/embedding', {
    text: embeddingForm.text
  }, {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Validation errors:', errors);
      isSubmitting.value = false;
    },
  });
}
</script>

<template>
  <BaseSlide>
    <template #title>Prism Text Embedding</template>
    <template #content>

      <p>Prism comes with functionality to generate AI embeddings that you can store in a vector database, such as Postgres.
          You can then use Laravel's Eloquent to search your database, allowing us to find documents relevant to the original prompt. </p>

      <p><span class="text-pink-400 font-bold ">Embeddings</span> consist of arrays of floating point numbers where each
        position represents some kind of learned
        feature from the input given. </p>

        <p>For example, the words "spatula" and "flipper" may not be similar in terms of their spelling or pronunciation,
            but they are semantically similar because they refer to the same type of object.</p>

        <VueCodeBlock highlightjs lang="php" :code=sampleCode />

        <div class="mt-6">
          <h4 class="text-orange-300 font-bold mb-3">Live Embedding Generator</h4>
          <form @submit.prevent="getEmbedding">
            <div class="flex items-center mb-3">
              <input
                  v-model="embeddingForm.text"
                  type="text"
                  id="embedding-text"
                  class="flex-1 mr-2 border border-gray-300 rounded-lg p-2 bg-gray-800/40 border-orange-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  placeholder="Enter text to generate embeddings..."
                  required
              />
              <button
                  type="submit"
                  class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200 flex items-center gap-2"
                  :disabled="embeddingForm.processing"
              >
                <LoadingSpinner v-if="embeddingForm.processing" size="16"/>
                <span>{{ embeddingForm.processing ? 'Generating...' : 'Generate' }}</span>
              </button>
            </div>
          </form>

          <div v-if="embeddingResponse" class="mt-4">
            <div class="mb-2 text-sm text-gray-400">
              <span class="font-semibold">Dimensions:</span> {{ embeddingResponse.total_dimensions }}
              <span class="ml-4 font-semibold">Showing:</span> First 50 values
            </div>
            <code class="bg-gray-800/20 rounded p-3 block text-xs overflow-x-auto whitespace-pre-wrap">
              [{{ embeddingResponse.embeddings.map(v => v.toFixed(6)).join(', ') }}...]
            </code>
          </div>
        </div>




<!--      <div class="my-2">
        <a href="https://youtu.be/gl1r1XV0SLw?si=RbajQ4ARJ7bByeON&t=139" target="_blank">
          <img :src=YtIcon alt="YouTube Icon" class="bg-white rounded-lg shadow-lg p-2 inline-block" width="50" />
          Vector Databases and RAG video
        </a>
      </div>-->

    </template>

    <template #footer>
      <div class="flex flex-col w-1/2 mx-auto">
        <code class="bg-gray-800/20 p-2 rounded-lg">
          ollama:text-embed
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
