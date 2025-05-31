<script setup>

</script>

<template>
  <BaseSlide>
    <template #title><span class="text-pink-500">RAG</span> Retrieval Augmented Generation</template>
    <template #content>
      <div class="flex">
        <div class="flex-1 pr-8">
          <p>RAG is a machine learning technique that incorporates external knowledge into a prompt sent to an LLM.
            The model's response is based not only on its internal knowledge but also on the additional information provided
            in the prompt.</p>

          <h4 class="text-2xl font-bold text-pink-400 my-4">Initial User Prompt</h4>

          <div class="mb-3">
            <span class="text-orange-500 ml-2">&rightarrow;</span>
            Pre-filtering, pre-processing, guardrails, formatting<br>
            <span class="text-orange-500  ml-4">&rightarrow;</span>
                    Find semantically related documents in vector datastore<br>
            <span class="text-orange-500 ml-6">&rightarrow;</span>
                    User prompt + matching documents used as context<br>

            <span class="text-orange-500 ml-8">&equals;</span>
            <span class="text-2xl text-yellow-400 my-2 ml-2">Better (hopefully) response</span>
          </div>
        </div>

        <div class="flex-shrink-0">
          <svg width="300" height="350" viewBox="0 0 350 440" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <marker id="arrowRAG" markerWidth="10" markerHeight="7" 
                      refX="9" refY="3.5" orient="auto" fill="#fb923c">
                <polygon points="0 0, 10 3.5, 0 7"/>
              </marker>
              <linearGradient id="ragGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#f472b6;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#fb923c;stop-opacity:1" />
              </linearGradient>
            </defs>
            
            <!-- User Input -->
            <rect x="95" y="20" width="160" height="40" rx="8" fill="#1f2937" stroke="#f472b6" stroke-width="2"/>
            <text x="175" y="35" text-anchor="middle" fill="#f472b6" font-size="13" font-weight="bold">User Prompt</text>
            <text x="175" y="50" text-anchor="middle" fill="#9ca3af" font-size="10">"Tell me about..."</text>
            
            <!-- Arrow down -->
            <path d="M 175 60 L 175 85" stroke="#fb923c" stroke-width="2" marker-end="url(#arrowRAG)"/>
            
            <!-- Pre-processing -->
            <rect x="85" y="95" width="180" height="40" rx="8" fill="#374151" stroke="#fb923c" stroke-width="2"/>
            <text x="175" y="110" text-anchor="middle" fill="#fb923c" font-size="11" font-weight="bold">Pre-processing</text>
            <text x="175" y="125" text-anchor="middle" fill="#9ca3af" font-size="9">Filter, Format, Guardrails</text>
            
            <!-- Arrow down -->
            <path d="M 175 135 L 175 160" stroke="#fb923c" stroke-width="2" marker-end="url(#arrowRAG)"/>
            
            <!-- Vector Database Search -->
            <rect x="85" y="170" width="180" height="55" rx="8" fill="#374151" stroke="#fb923c" stroke-width="2"/>
            <text x="175" y="190" text-anchor="middle" fill="#fb923c" font-size="11" font-weight="bold">Vector Search</text>
            <text x="175" y="205" text-anchor="middle" fill="#9ca3af" font-size="9">Find semantically</text>
            <text x="175" y="218" text-anchor="middle" fill="#9ca3af" font-size="9">related documents</text>
            
            <!-- Database icon to the right -->
            <g transform="translate(275, 180)">
              <ellipse cx="30" cy="6" rx="25" ry="5" fill="#1f2937" stroke="#fb923c" stroke-width="1"/>
              <rect x="5" y="6" width="50" height="18" fill="#1f2937"/>
              <ellipse cx="30" cy="24" rx="25" ry="5" fill="#1f2937" stroke="#fb923c" stroke-width="1"/>
              <path d="M 5 6 L 5 24" stroke="#fb923c" stroke-width="1"/>
              <path d="M 55 6 L 55 24" stroke="#fb923c" stroke-width="1"/>
              <text x="30" y="18" text-anchor="middle" fill="#fb923c" font-size="8">DB</text>
            </g>
            
            <!-- Arrow down -->
            <path d="M 175 225 L 175 250" stroke="#fb923c" stroke-width="2" marker-end="url(#arrowRAG)"/>
            
            <!-- Augmentation -->
            <rect x="50" y="260" width="250" height="40" rx="8" fill="#374151" stroke="#fb923c" stroke-width="2"/>
            <text x="175" y="275" text-anchor="middle" fill="#fb923c" font-size="11" font-weight="bold">Prompt Augmentation</text>
            <text x="175" y="290" text-anchor="middle" fill="#9ca3af" font-size="9">User prompt + Retrieved context</text>
            
            <!-- Arrow down -->
            <path d="M 175 300 L 175 320" stroke="#fb923c" stroke-width="2" marker-end="url(#arrowRAG)"/>
            
            <!-- LLM -->
            <circle cx="175" cy="340" r="25" fill="#1f2937" stroke="url(#ragGrad)" stroke-width="2.5"/>
            <text x="175" y="340" text-anchor="middle" fill="#f472b6" font-size="11" font-weight="bold">LLM</text>
            
            <!-- Arrow down -->
            <path d="M 175 365 L 175 380" stroke="#fbbf24" stroke-width="3" marker-end="url(#arrowRAG)"/>
            
            <!-- Enhanced Response -->
            <rect x="75" y="390" width="200" height="40" rx="8" fill="#1f2937" stroke="#fbbf24" stroke-width="2.5"/>
            <text x="175" y="405" text-anchor="middle" fill="#fbbf24" font-size="12" font-weight="bold">Enhanced Response</text>
            <text x="175" y="420" text-anchor="middle" fill="#9ca3af" font-size="9">Context-aware answer</text>
            
            <!-- Flow indicators -->
            <text x="15" y="45" fill="#6b7280" font-size="9">1</text>
            <text x="15" y="115" fill="#6b7280" font-size="9">2</text>
            <text x="15" y="195" fill="#6b7280" font-size="9">3</text>
            <text x="15" y="280" fill="#6b7280" font-size="9">4</text>
            <text x="15" y="340" fill="#6b7280" font-size="9">5</text>
            <text x="15" y="410" fill="#6b7280" font-size="9">6</text>
          </svg>
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