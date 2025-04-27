#!/bin/bash
/bin/ollama serve &

# Record Process ID
pid=$!

# Pause for Ollama to start
sleep 5

echo "loading mxbai-embed-large"

ollama pull mxbai-embed-large

wait $pid

