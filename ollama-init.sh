#!/bin/bash
/bin/ollama serve &

# Record Process ID
pid=$!

# Pause for Ollama to start
sleep 5

# Check if we should skip model installation
if [ "${SKIP_OLLAMA_MODELS}" = "true" ]; then
    echo "=================================================="
    echo "⚠️  SKIPPING OLLAMA MODEL INSTALLATION"
    echo "=================================================="
    echo ""
    echo "To install models later, run:"
    echo "  ./ollama-install-models.sh"
    echo ""
    echo "Or manually with docker:"
    echo "  docker exec -it ollama ollama pull llama3.2"
    echo "  docker exec -it ollama ollama pull qwen3:4b"
    echo "  docker exec -it ollama ollama pull mxbai-embed-large"
    echo ""
    echo "=================================================="
else
    echo "Installing Ollama models (this may take a while)..."
    echo "To skip this in the future, set SKIP_OLLAMA_MODELS=true"
    echo ""
    
    echo "loading llama3.2"
    ollama pull llama3.2
    echo "loading qwen3:4b"
    ollama pull qwen3:4b
    echo "loading mxbai-embed-large"
    ollama pull mxbai-embed-large
    
    echo ""
    echo "✅ Models installed successfully!"
fi

# Additional models (commented out for reference)
# ollama pull mistral
# ollama pull mwiewior/bielik
# ollama pull deepseek-r1:7b
# ollama pull deepseek-coder-v2

wait $pid

