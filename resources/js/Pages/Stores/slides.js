import {ref, computed} from 'vue'

export const useSlides = () => {
    const currentSlideIndex = ref(0)

    const slides = [
        'Slide1',
        'Slide2',
        'Slide3',
        'Slide4',
        'Slide5',
        'Ollama',
        'AiProviders',
        'Sail',
        'Slide6',
        'Slide7',
        'Tools',
        'TcRei',
        'Slide8',
        'TextEmbedding',
        'Slide9',
        'FormatPrompt',
        'ExtractKeywords',
        'SearchDb',
        'FinalSongPrompt',
        'WeirdAlLives',
        'MCP',
        'Slide10',
        'PrismServerHttp',
        'MCPServerList',
        'Slide11',
        'ChatWithTools',
    ]

    const next = computed(() =>
        currentSlideIndex.value < slides.length - 1
            ? `/slides/${slides[currentSlideIndex.value + 1]}`
            : null
    )

    const previous = computed(() =>
        currentSlideIndex.value > 0
            ? `/slides/${slides[currentSlideIndex.value - 1]}`
            : null
    )

    const setCurrentIndex = (path) => {
        // Extract slide name from path
        const slideName = path.split('/').pop()

        if(slideName === null || slideName === '') {
            currentSlideIndex.value = 0
            return
        }

        currentSlideIndex.value = slides.findIndex(slide => slide === slideName)
    }

    return {
        slides,
        next,
        previous,
        setCurrentIndex
    }
}
