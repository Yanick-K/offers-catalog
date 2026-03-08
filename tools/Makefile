ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/..)
SAIL := $(ROOT)/vendor/bin/sail
OFFERS ?= 3
PRODUCTS ?= 3

.PHONY: sail-up sail-down sail-restart sail-logs setup migrate seed seed-base dev test phpstan deptrac pint lint ci test-all

sail-up:
	$(SAIL) up -d

sail-down:
	$(SAIL) down

sail-restart:
	$(SAIL) down
	$(SAIL) up -d

sail-logs:
	$(SAIL) logs -f

setup:
	$(SAIL) composer install
	$(SAIL) npm ci
	$(SAIL) artisan key:generate
	$(SAIL) artisan migrate --seed
	$(SAIL) artisan storage:link

migrate:
	$(SAIL) artisan migrate

seed:
	$(SAIL) artisan demo:seed --offers=$(OFFERS) --products=$(PRODUCTS)

seed-base:
	$(SAIL) artisan db:seed

dev:
	$(SAIL) php artisan serve & \
	$(SAIL) php artisan queue:work

test:
	$(SAIL) artisan migrate
	$(SAIL) php artisan test

phpstan:
	$(SAIL) ./vendor/bin/phpstan analyse

deptrac:
	$(SAIL) ./vendor/bin/deptrac analyse --config-file=deptrac.yaml

pint:
	$(SAIL) ./vendor/bin/pint

lint:
	$(SAIL) ./vendor/bin/pint --test

ci: lint phpstan deptrac test

test-all: ci
