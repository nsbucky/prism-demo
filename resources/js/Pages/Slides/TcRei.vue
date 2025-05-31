<script setup>
</script>

<template>
    <BaseSlide>
        <template #title><span class="text-pink-400 font-bold">T</span>houghtfully
            <span class="text-pink-400 font-bold">C</span>reate
            <span class="text-pink-400 font-bold">R</span>eally
            <span class="text-pink-400 font-bold">E</span>xcellent
            <span class="text-pink-400 font-bold">I</span>nput</template>
        <template #content>
            <div class="flex">
                <div class="flex-1 pr-8">
                    <p class="mb-8">This prompting framework helps improve the performance of language models by using reinforcement learning
                        techniques to fine-tune the model based on human feedback, enhancing the model's ability to generate
                        coherent and contextually relevant text.</p>

                    <dl class="grid grid-cols-3 gap-4 min-w-md">
                        <dt class="font-bold text-right col-span-1"><span class="text-pink-500">T</span>ask</dt>
                        <dd class="mb-4 col-span-2">What do you want it to do?</dd>
                        <dt class="font-bold text-right col-span-1"><span class="text-pink-500">C</span>ontext</dt>
                        <dd class="mb-4 col-span-2">Provide relevant information to understand the task.</dd>
                        <dt class="font-bold text-right col-span-1"><span class="text-pink-500">R</span>eferences</dt>
                        <dd class="mb-4 col-span-2">Examples of similar tasks to guide its response.</dd>
                        <dt class="font-bold text-right col-span-1"><span class="text-pink-500">E</span>valuate</dt>
                        <dd class="mb-4 col-span-2">Assess output to determine if it meets the desired criteria.</dd>
                        <dt class="font-bold text-right col-span-1"><span class="text-pink-500">I</span>terate</dt>
                        <dd class="mb-4 col-span-2">Make changes, try again!</dd>
                    </dl>
                </div>
                
                <div class="flex-shrink-0">
                    <svg width="300" height="300" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg">
                        <!-- Central circle -->
                        <circle cx="150" cy="150" r="40" fill="#1f2937" stroke="#f472b6" stroke-width="2"/>
                        <text x="150" y="155" text-anchor="middle" fill="#f472b6" font-size="14" font-weight="bold">TCREI</text>
                        
                        <!-- Outer circles for each step -->
                        <!-- Task (top) -->
                        <circle cx="150" cy="50" r="35" fill="#374151" stroke="#f472b6" stroke-width="2"/>
                        <text x="150" y="55" text-anchor="middle" fill="#f472b6" font-size="24" font-weight="bold">T</text>
                        
                        <!-- Context (top-right) -->
                        <circle cx="235" cy="100" r="35" fill="#374151" stroke="#f472b6" stroke-width="2"/>
                        <text x="235" y="105" text-anchor="middle" fill="#f472b6" font-size="24" font-weight="bold">C</text>
                        
                        <!-- References (bottom-right) -->
                        <circle cx="235" cy="200" r="35" fill="#374151" stroke="#f472b6" stroke-width="2"/>
                        <text x="235" y="205" text-anchor="middle" fill="#f472b6" font-size="24" font-weight="bold">R</text>
                        
                        <!-- Evaluate (bottom) -->
                        <circle cx="150" cy="250" r="35" fill="#374151" stroke="#f472b6" stroke-width="2"/>
                        <text x="150" y="255" text-anchor="middle" fill="#f472b6" font-size="24" font-weight="bold">E</text>
                        
                        <!-- Iterate (left) -->
                        <circle cx="65" cy="150" r="35" fill="#374151" stroke="#f472b6" stroke-width="2"/>
                        <text x="65" y="155" text-anchor="middle" fill="#f472b6" font-size="24" font-weight="bold">I</text>
                        
                        <!-- Curved arrows connecting the steps -->
                        <defs>
                            <marker id="arrowhead" markerWidth="10" markerHeight="7" 
                                    refX="9" refY="3.5" orient="auto" fill="#f472b6">
                                <polygon points="0 0, 10 3.5, 0 7"/>
                            </marker>
                        </defs>
                        
                        <!-- T to C -->
                        <path d="M 180 65 Q 210 80 210 115" fill="none" stroke="#f472b6" stroke-width="2" marker-end="url(#arrowhead)"/>
                        
                        <!-- C to R -->
                        <path d="M 235 135 Q 235 165 235 165" fill="none" stroke="#f472b6" stroke-width="2" marker-end="url(#arrowhead)"/>
                        
                        <!-- R to E -->
                        <path d="M 210 215 Q 180 230 180 235" fill="none" stroke="#f472b6" stroke-width="2" marker-end="url(#arrowhead)"/>
                        
                        <!-- E to I -->
                        <path d="M 120 235 Q 90 200 90 165" fill="none" stroke="#f472b6" stroke-width="2" marker-end="url(#arrowhead)"/>
                        
                        <!-- I back to T (iterate) -->
                        <path d="M 90 125 Q 120 90 120 65" fill="none" stroke="#f472b6" stroke-width="2" marker-end="url(#arrowhead)" stroke-dasharray="5,5"/>
                        
                        <!-- Labels for each step -->
                        <text x="150" y="25" text-anchor="middle" fill="#9ca3af" font-size="12">Task</text>
                        <text x="265" y="105" text-anchor="start" fill="#9ca3af" font-size="12">Context</text>
                        <text x="265" y="205" text-anchor="start" fill="#9ca3af" font-size="12">References</text>
                        <text x="150" y="280" text-anchor="middle" fill="#9ca3af" font-size="12">Evaluate</text>
                        <text x="35" y="155" text-anchor="end" fill="#9ca3af" font-size="12">Iterate</text>
                    </svg>
                </div>
            </div>
        </template>
    </BaseSlide>
</template>

<style scoped>
    dt {
        color: #f472b6; /* Tailwind pink-500 */
    }

</style>
