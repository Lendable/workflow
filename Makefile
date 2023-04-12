ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
UID:=$(shell id -u)
GID:=$(shell id -g)

COMMAND_ARGS=$(filter-out $@,$(MAKECMDGOALS))

default: help

help: ## Display this help
	@ echo "Please use \`make <target>' where <target> is one of:"
	@ echo
	@ grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}; {printf "    \033[36m%-15s\033[0m - %s\n", $$1, $$2}' | sort
	@ echo

deps: ## Install dependencies
deps: docker-build
deps: COMMAND_ARGS=install
deps: composer

.PHONY: docker-build
docker-build: ## Build Docker image
docker-build:
	@ docker build -t lendable/hackathon ./infrastructure

.PHONY: docker-clean
docker-clean: ## Clean everything
docker-clean:
	@ docker rmi lendable/hackathon || echo 'There is no image to delete'

.PHONY: docker-rebuild
docker-rebuild: ## Rebuild Docker image
docker-rebuild: clean docker-build

.PHONY: composer
composer: ## Run composer
composer:
	@ docker run -it --rm --user ${UID}:${GID} -v "${ROOT_DIR}:/app/" -w=/app --name lendable-hackathon --entrypoint=composer lendable/hackathon ${COMMAND_ARGS}

.PHONY: phpunit
phpunit: ## Run tests
phpunit:
	@ docker run -it --rm --user ${UID}:${GID} -v "${ROOT_DIR}:/app/" -w=/app --name lendable-hackathon --entrypoint=phpunit lendable/hackathon

.PHONY: infection
infection: ## Run tests
infection:
	@ docker run -it --rm --user ${UID}:${GID} -v "${ROOT_DIR}:/app/" -w=/app --name lendable-hackathon --entrypoint=infection lendable/hackathon run --threads=16

.PHONY: bash
bash: ## Run bash
bash:
	@ docker run -it --rm --user ${UID}:${GID} -v "${ROOT_DIR}:/app/" -w=/app --user root --name lendable-hackathon --entrypoint=/bin/ash lendable/hackathon

.PHONY: magic
magic: ## Run AI magic
magic:
	@ docker run -it --rm --user ${UID}:${GID} -v "${ROOT_DIR}:/app/" --env OPENAI_KEY -w=/app --user root --name lendable-hackathon --entrypoint=php lendable/hackathon hackathon/api-call.php

### Argument fix workaround
%:
	@:
