# use prism php
https://prismphp.com/getting-started/introduction.html

![[Pasted image 20250426091352.png]]

It supports these Providers:
![[Pasted image 20250426091431.png]]

## Installation
```
composer require prism-php/prism
php artisan vendor:publish --tag=prism-config
```

this is in branch main 2nd commit

run artisan migrate && db:seed

# Sail installation
```
sail up -d
```

make sure ollama is working
```
# in env set url
OLLAMA_URL=http://ollama:11434

# get name of container
docker ps

# get into its "back-end"
docker exec -it ollama:latest /bin/bash

ollama pull llama3.2
```
### make a sample command
```
sail artisan make:command OllamaSpitsCommand --command ai:spit
```


### writing tests
https://pestphp.com/docs/writing-tests

**Stuck on writing tests for the Tools in Prism/Ollama**

# filament
