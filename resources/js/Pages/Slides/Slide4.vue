<script setup>

</script>

<template>
  <BaseSlide next="/slides/5" previous="/slides/3">
    <template #title>Laravel Framework</template>
    <template #content>
      <div class="grid grid-cols-6">
        <div class="col-span-2 p-4">
          <img crossorigin="anonymous"
               src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Laravel.svg/800px-Laravel.svg.png"
               class="bg-white rounded-lg shadow-lg p-4 mb-2" alt="Laravel Logo"/>
              <a href="https://laravel.com" class="text-red">laravel.com</a>
        </div>
        <div class="col-span-4 p-8">
          <p>Laravel is a robust, progressive, PHP framework renowned for its elegant syntax, developer-friendly tools, and
            extensive ecosystem.</p>

          <p>This presentation uses Laravel v12 &amp; PHP 8.4</p>

          </div>
      </div>
    </template>
  </BaseSlide>
</template>

<style scoped>
p {
  margin-bottom: 1rem;
}
</style>


