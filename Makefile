.DEFAULT_GOAL := help
SHELL=/bin/bash
SYMFONY=cd tests/Application && symfony
COMPOSER=symfony composer
CONSOLE=${SYMFONY} console
DOCKER-COMPOSE=docker-compose
YARN=cd tests/Application && yarn

###
### DEVELOPMENT
### ¯¯¯¯¯¯¯¯¯¯¯

install: platform sylius ## Install the plugin
.PHONY: install

up: docker.up server.start ## Up the project (start docker, start symfony server)
stop: server.stop docker.stop ## Stop the project (stop docker, stop symfony server)
down: server.stop docker.down ## Down the project (removes docker containers, stop symfony server)

reset: docker.down ## Stop docker and remove dependencies
	rm -rf tests/Application/{node_modules} tests/Application/yarn.lock
	rm -rf vendor composer.lock
.PHONY: reset

dependencies: vendor node_modules ## Setup the dependencies
.PHONY: dependencies

.php-version: .php-version.dist
	cp .php-version.dist .php-version
	(cd tests/Application && ln -sf ../../.php-version)

vendor: composer.lock ## Install the PHP dependencies using composer
	${COMPOSER} install --prefer-source

composer.lock: composer.json
	${COMPOSER} update

yarn.install: tests/Application/yarn.lock

tests/Application/yarn.lock:
	${YARN} install
	${YARN} build

node_modules: tests/Application/node_modules ## Install the Node dependencies using yarn

tests/Application/node_modules: yarn.install

###
### TESTS
### ¯¯¯¯¯

test.phpcs: ## Run PHP CS Fixer in dry-run
	${COMPOSER} run -- phpcs --dry-run -v

test.phpcs.fix: ## Run PHP CS Fixer and fix issues if possible
	${COMPOSER} run -- phpcs -v

test.container: ## Lint the symfony container
	${CONSOLE} lint:container

test.yaml: ## Lint the symfony Yaml files
	${CONSOLE} lint:yaml ../../recipes ../../src/Resources/config

test.schema: ## Validate MySQL Schema
	${CONSOLE} doctrine:schema:validate

test.twig: ## Validate Twig templates
	${CONSOLE} lint:twig -e prod --no-debug templates/ ../../src/Resources/views/

###
### SYLIUS
### ¯¯¯¯¯¯

sylius: dependencies sylius.database sylius.fixtures ## Install Sylius
.PHONY: sylius

sylius.database: ## Setup the database
	${CONSOLE} doctrine:database:drop --if-exists --force
	${CONSOLE} doctrine:database:create --if-not-exists
	${CONSOLE} doctrine:schema:update --force

sylius.fixtures: ## Run the fixtures
	${CONSOLE} sylius:fixtures:load -n default

###
### PLATFORM
### ¯¯¯¯¯¯¯¯

platform: .php-version up ## Setup the platform tools
.PHONY: platform

docker.pull: ## Pull the docker images
	cd tests/Application && ${DOCKER-COMPOSE} pull

docker.up: ## Start the docker containers
	cd tests/Application && ${DOCKER-COMPOSE} up -d
.PHONY: docker.up

docker.stop: ## Stop the docker containers
	cd tests/Application && ${DOCKER-COMPOSE} stop
.PHONY: docker.stop

docker.down: ## Stop and remove the docker containers
	cd tests/Application && ${DOCKER-COMPOSE} down
.PHONY: docker.down

server.start: ## Run the local webserver using Symfony
	${SYMFONY} local:proxy:domain:attach settings
	${SYMFONY} local:server:start -d

server.stop: ## Stop the local webserver
	${SYMFONY} local:server:stop

###
### HELP
### ¯¯¯¯

help: SHELL=/bin/bash
help: ## Dislay this help
	@IFS=$$'\n'; for line in `grep -h -E '^[a-zA-Z_#-]+:?.*?##.*$$' $(MAKEFILE_LIST)`; do if [ "$${line:0:2}" = "##" ]; then \
	echo $$line | awk 'BEGIN {FS = "## "}; {printf "\033[33m    %s\033[0m\n", $$2}'; else \
	echo $$line | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m%s\n", $$1, $$2}'; fi; \
	done; unset IFS;
.PHONY: help
