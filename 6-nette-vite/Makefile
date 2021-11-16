# ################################################
# Development ####################################
# ################################################
.PHONY: setup install install-php install-npm dev assets build project qa cs csf phpstan

setup: install project ## Setup project

project: ## Setup project
	cp -n config/local.neon.dist config/local.neon | true
	mkdir -p var
	chmod 0777 var

install: install-php install-npm ## Install dependencies

install-php: ## Install PHP dependencies
	composer install

install-npm: ## Install NPM dependencies
	npm ci

dev: ## Start PHP development server
	NETTE_DEBUG=1 php -S 0.0.0.0:8000 -t www

assets: ## Compile assets
	npm run watch

serve: ## Serve assets via vite dev server
	npm run serve

build: ## Build assets
	npm run build

qa: cs phpstan ## Check code quality - coding style and static analysis

cs: ## Check PHP files coding style
	vendor/bin/phpcs --cache=var/codesniffer.dat --standard='ruleset.xml' app

csf: ## Fix PHP files coding style
	vendor/bin/phpcbf --cache=var/codesniffer.dat --standard='ruleset.xml' app

phpstan: ## Analyse code with PHPStan
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=512M app

# ################################################
# Tests ##########################################
# ################################################
.PHONY: test

test: ## Run all tests
	vendor/bin/tester tests/cases --colors 1 -s -p php -C
