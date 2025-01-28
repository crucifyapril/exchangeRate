IMAGE = symfony
VERSION = 1.0
WORK_DIR = /var/www

COMPOSER_IMAGE=registry.gitlab.com/img-docker/composer

export IMAGE
export VERSION
export WORK_DIR

.PHONY: logs php env build up down restart clean clean-logs clean-vendor npm-install npm-build npm-dev npm-run

build:
	@docker compose build --build-arg IMAGE=$(IMAGE) --build-arg VERSION=$(VERSION)
up:
	@docker compose up -d
down:
	@docker compose down
restart:
	@docker compose down && docker-compose up -d

logs:
	@docker compose logs -f

vendor:
	@docker run -it --rm -v .:$(WORK_DIR) $(COMPOSER_IMAGE) install
require:
	@docker run -it --rm -v .:$(WORK_DIR) $(COMPOSER_IMAGE) require $1
env:
	@cp .env.example .env
# Пример: make php c='php artisan tinker'
php:
	@docker run -it --rm -v .:$(WORK_DIR) --network=web-symfony --user 1000:1000 $(IMAGE):$(VERSION) $(c)

clean: clean-logs clean-vendor
clean-logs:
	@sudo rm -fr ./.docker/logs/nginx/*
clean-vendor:
	@rm -fr ./vendor && rm -fr ./node_modules

npm-install:
	@docker run -it --rm -v $$(pwd):/app -w /app --user 1000:1000 node:22.11 npm i
npm-build:
	@docker run -it --rm -v $$(pwd):/app -w /app --user 1000:1000 node:22.11 npm run build
npm-dev:
	@docker run -it --rm -v $$(pwd):/app -w /app --user 1000:1000 -p 5173:5173 node:22.11 npm run dev

# Пример: make npm-run cmd='npm install -D tailwindcss'
npm-run:
	@docker run -it --rm -v $$(pwd):/app -w /app --user 1000:1000 node:22.11 $(cmd)
