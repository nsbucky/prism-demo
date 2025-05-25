#!/bin/bash

echo "=================================================="
echo "🦙 OLLAMA MODEL INSTALLER"
echo "=================================================="
echo ""
echo "This script will install the required Ollama models"
echo "for the Prism demo project."
echo ""

# Check if Ollama container is running
if ! docker ps | grep -q "ollama"; then
    echo "❌ Error: Ollama container is not running!"
    echo ""
    echo "Please start the containers first with:"
    echo "  ./vendor/bin/sail up -d"
    echo ""
    exit 1
fi

echo "Installing models (this may take several minutes)..."
echo ""

# Install required models
echo "📦 Installing llama3.2..."
docker exec -it ollama ollama pull llama3.2

echo ""
echo "📦 Installing qwen3:4b..."
docker exec -it ollama ollama pull qwen3:4b

echo ""
echo "📦 Installing mxbai-embed-large (for embeddings)..."
docker exec -it ollama ollama pull mxbai-embed-large

echo ""
echo "=================================================="
echo "✅ ALL MODELS INSTALLED SUCCESSFULLY!"
echo "=================================================="
echo ""
echo "You can now use all the demo features that require"
echo "these models."
echo ""

# List installed models
echo "Installed models:"
docker exec -it ollama ollama list