<script setup>
import VueCodeBlock from '@wdns/vue-code-block';

const sampleCode = `
POST http://localhost/prism/openai/v1/chat/completions
Content-Type: application/json

{
  "model": "spatula-creator",
  "messages": [
    {"role": "user", "content": "Hello, who are you?"}
  ]
}

GET http://localhost/prism/openai/v1/models`

</script>

<template>
    <BaseSlide>
        <template #title>MCP Http Requests</template>
        <template #content>
            <VueCodeBlock highlightjs :code=sampleCode />
        </template>
    </BaseSlide>
</template>
