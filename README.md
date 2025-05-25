# Installation
If you want to run this locally, you need to have Docker, PHP 8.4, and composer installed on your system.

## Quick Start (Without Ollama Models)
To just view the presentation without downloading large AI models (~6GB), set the environment variable:
```
SKIP_OLLAMA_MODELS=true composer setup
```

## Full Installation
To run the full demo with all AI features, use:
```
composer setup
```

If this does not work, you can also run the commands manually:
```
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail composer run dev
```

browse over to [the presentation](http://localhost:8080)

## Installing Ollama Models Later
If you skipped model installation, you can install them anytime by running:
```
./ollama-install-models.sh
```

## Play around with ollama 
```
# get name of container (usually ollama)
docker ps

# get into ollama
docker exec -it ollama /bin/bash

# pull any model your system can handle
#ollama pull llama3.2
#ollama pull deepseek-r1:1.5b

# send it a simple prompt
ollama run llama3.2 "Hello, how are you?"
```

