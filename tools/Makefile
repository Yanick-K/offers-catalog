ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/..)
SAIL := $(ROOT)/vendor/bin/sail
OFFERS ?= 3
PRODUCTS ?= 3

.PHONY: sail-up sail-down sail-restart sail-logs setup migrate seed seed-base reset dev test phpstan deptrac pint lint ci test-all

sail-up:
	"$(SAIL)" up -d

sail-down:
	"$(SAIL)" down

sail-restart:
	"$(SAIL)" down
	"$(SAIL)" up -d

sail-logs:
	"$(SAIL)" logs -f

setup: sail-up
	"$(SAIL)" composer install
	"$(SAIL)" npm ci
	"$(SAIL)" artisan key:generate
	"$(SAIL)" artisan migrate --seed
	"$(SAIL)" artisan storage:link

migrate: sail-up
	"$(SAIL)" artisan migrate

seed: sail-up
	"$(SAIL)" artisan demo:seed --offers=$(OFFERS) --products=$(PRODUCTS)

seed-base: sail-up
	"$(SAIL)" artisan db:seed

reset: sail-up
	"$(SAIL)" artisan migrate:fresh --seed

dev: sail-up
	"$(SAIL)" php artisan serve & \
	"$(SAIL)" php artisan queue:work

test: sail-up
	"$(SAIL)" artisan migrate
	"$(SAIL)" php artisan test

phpstan: sail-up
	"$(SAIL)" ./vendor/bin/phpstan analyse

deptrac: sail-up
	"$(SAIL)" ./vendor/bin/deptrac analyse --config-file=deptrac.yaml

pint: sail-up
	"$(SAIL)" ./vendor/bin/pint

lint: sail-up
	"$(SAIL)" ./vendor/bin/pint --test

ci: sail-up lint phpstan deptrac test

test-all: ci
