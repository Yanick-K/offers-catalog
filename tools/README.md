# Tools

## Makefile commands

- `make setup` – install dependencies and initialize the project
- `make reset` – reset the environment (migrations + seed)
- `make dev` – start local dev environment
- `make dev-detach` – start dev environment in background
- `make dev-stop` – stop dev environment
- `make lint` – run code style checks
- `make test-all` (alias: `make ci`) – run lint, static analysis and tests
- `make seed OFFERS=10 PRODUCTS=5` – generate demo data
- `make seed-base` – run default seeders
- `make seed-remote OFFERS=10 PRODUCTS=5` – generate demo data with remote images

## Bash scripts

- `sh tools/dev.sh --setup`
- `sh tools/dev.sh --reset`
- `sh tools/dev.sh --detach`
- `sh tools/dev.sh --stop`
- `sh tools/test-all.sh`

## Artisan demo data

- `php artisan demo:seed --offers=10 --products=5`
- `php artisan demo:seed --offers=10 --products=5 --remote`
