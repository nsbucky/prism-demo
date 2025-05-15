<script setup>
</script>

<template>
    <BaseSlide>
        <template #title><span class="text-pink-500">MCP</span> Model Context Protocol</template>
        <template #content>
            <p>

                The Model Context Protocol (MCP) is an open-source standard developed by Anthropic to enable seamless
                and standardized interaction between AI models (like Ollama) and external tools, data sources, and
                environments. It acts as a universal connector, simplifying how AI models access and utilize external resources, like
                APIs, databases, and business applications.
            </p>

            <h2 class="text-pink-300 text-center font-bold text-[48px] text-center">Prism is your AI bridge</h2>

            <h3 class="text-pink-500 font-bold text-2xl my-4">
            Prism offers key components of MCP:
            </h3>

            <p>
                <span class="font-bold text-orange-500">Client:</span><br>
            Part of the AI application (e.g., a chatbot or IDE) that initiate connections with MCP servers.
            </p>

            <p><span class="font-bold text-orange-500">Server</span><br>
            Provide access to external tools, data sources, and services, handling communication with the client.
            </p>

        </template>
    </BaseSlide>
</template>
<style scoped>
p {
    margin-bottom: 1rem;
}
</style>
