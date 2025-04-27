#!/bin/bash
/bin/ollama serve &

# Record Process ID
pid=$!

# Pause for Ollama to start
sleep 5
echo "loading llama3.2"
ollama pull llama3.2
echo "loading mxbai-embed-large"
ollama pull mxbai-embed-large
# ollama pull mistral
# ollama pull mwiewior/bielik
# ollama pull deepseek-r1:7b
# ollama pull deepseek-coder-v2
# For embedding capabilities, always pull the mxbai model
# ollama pull mxbai-embed-large

wait $pid

