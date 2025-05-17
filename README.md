# Installation
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

browse over to [url=http://localhost]http://localhost


Play around with ollama 
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

