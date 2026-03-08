ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/..)
OFFERS ?= 3
PRODUCTS ?= 3

.PHONY: setup reset dev dev-detach dev-stop lint pint phpstan deptrac test test-all ci seed seed-base seed-remote

setup:
	sh "$(ROOT)/tools/dev.sh" --setup

reset:
	sh "$(ROOT)/tools/dev.sh" --reset

dev:
	sh "$(ROOT)/tools/dev.sh"

dev-detach:
	sh "$(ROOT)/tools/dev.sh" --detach

dev-stop:
	sh "$(ROOT)/tools/dev.sh" --stop

seed:
	php artisan demo:seed --offers=$(OFFERS) --products=$(PRODUCTS)

seed-base:
	php artisan db:seed

seed-remote:
	php artisan demo:seed --offers=$(OFFERS) --products=$(PRODUCTS) --remote

lint:
	./vendor/bin/pint --test

pint:
	./vendor/bin/pint

phpstan:
	./vendor/bin/phpstan analyse

deptrac:
	./vendor/bin/deptrac analyse --config-file=deptrac.yaml

test:
	php artisan migrate
	php artisan test

test-all:
	sh "$(ROOT)/tools/test-all.sh"

ci: lint phpstan deptrac test
