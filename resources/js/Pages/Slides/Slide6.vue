<script setup>
import BaseSlide from "../Components/BaseSlide.vue";
</script>

<template>
    <BaseSlide next="/slides/7" previous="/slides/5">
        <template #title>Talking to the Ollama</template>
        <template #content>
            Basic Text generation example

            OllamaSpitsCommand and show test

            *Show response handing*
            *show error handling*

            Show more advanced tweaky usage

            Tuning the command using temperature or Top and max tokens

            Fine-tune your generations with various parameters:

            `withMaxTokens`

            Maximum number of tokens to generate.

            `usingTemperature`

            Temperature setting.

            The value is passed through to the provider. The range depends on the provider and model. For most providers, 0 means almost deterministic results, and higher values mean more randomness.

            TIP

            It is recommended to set either temperature or topP, but not both.

            `usingTopP`

            Nucleus sampling.

            The value is passed through to the provider. The range depends on the provider and model. For most providers, nucleus sampling is a number between 0 and 1. E.g. 0.1 would mean that only tokens with the top 10% probability mass are considered.

            TIP

            It is recommended to set either temperature or topP, but not both.

            `withClientOptions`

            Under the hood we use Laravel's [HTTP client](https://laravel.com/docs/11.x/http-client#main-content). You can use this method to pass any of Guzzles [request options](https://docs.guzzlephp.org/en/stable/request-options.html) e.g. `->withClientOptions(['timeout' => 30])`.

            `withClientRetry`

            Under the hood we use Laravel's [HTTP client](https://laravel.com/docs/11.x/http-client#main-content). You can use this method to set [retries](https://laravel.com/docs/11.x/http-client#retries) e.g. `->withClientRetry(3, 100)`.

            `usingProviderConfig`

            This allows for complete or partial override of the providers configuration. This is great for multi-tenant applications where users supply their own API keys. These values are merged with the original configuration allowing for partial or complete config override.

        </template>
    </BaseSlide>
</template>
