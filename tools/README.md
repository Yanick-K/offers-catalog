# Tools

## Commandes de base (local)
- `make setup`
- `make reset`
- `make dev`
- `make dev-detach`
- `make dev-stop`
- `make lint`
- `make test-all` (or `make ci`)
- `make seed OFFERS=10 PRODUCTS=5` (or `make seed-base`)
- `make seed-remote OFFERS=10 PRODUCTS=5`

## Makefile (tools)
- `make -f tools/Makefile setup`
- `make -f tools/Makefile reset`
- `make -f tools/Makefile lint`
- `make -f tools/Makefile phpstan`
- `make -f tools/Makefile deptrac`
- `make -f tools/Makefile test`
- `make -f tools/Makefile test-all`
- `make -f tools/Makefile ci`
- `make -f tools/Makefile dev`
- `make -f tools/Makefile dev-detach`
- `make -f tools/Makefile dev-stop`
- `make -f tools/Makefile seed OFFERS=10 PRODUCTS=5`
- `make -f tools/Makefile seed-base`
- `make -f tools/Makefile seed-remote OFFERS=10 PRODUCTS=5`

## Scripts
- `sh tools/dev.sh --setup`
- `sh tools/dev.sh --reset`
- `sh tools/dev.sh --detach`
- `sh tools/dev.sh --stop`
- `sh tools/test-all.sh`
- `php artisan demo:seed --offers=10 --products=5 --remote`
