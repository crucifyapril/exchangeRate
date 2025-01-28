IMAGE = symfony
VERSION = 1.0
WORK_DIR = /var/www

COMPOSER_IMAGE=registry.gitlab.com/img-docker/composer

export IMAGE
export VERSION
export WORK_DIR

.PHONY: php build up down clean clean-logs clean-vendor

build:
	@docker compose build --build-arg IMAGE=$(IMAGE) --build-arg VERSION=$(VERSION)
up:
	@docker compose up -d
down:
	@docker compose down
vendor:
	@docker run --rm -it --user 1000:1000 -v $(pwd):/app composer install

# Пример: make php c='php artisan tinker'
php:
	@docker run -it --rm -v .:$(WORK_DIR) --network=web-symfony --user 1000:1000 $(IMAGE):$(VERSION) $(c)

clean: clean-logs clean-vendor
clean-logs:
	@sudo rm -fr ./.docker/logs/nginx/*
clean-vendor:
	@rm -fr ./vendor && rm -fr ./node_modules
