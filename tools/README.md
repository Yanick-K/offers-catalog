# Tools

## Quick (Sail)
- `make setup`
- `make reset`
- `make dev`
- `make test-all` (or `make ci`)
- `make seed OFFERS=10 PRODUCTS=5` (or `make seed-base`)

## Makefile (tools)
- `make -f tools/Makefile sail-up`
- `make -f tools/Makefile setup`
- `make -f tools/Makefile reset`
- `make -f tools/Makefile lint`
- `make -f tools/Makefile phpstan`
- `make -f tools/Makefile deptrac`
- `make -f tools/Makefile test`
- `make -f tools/Makefile test-all`
- `make -f tools/Makefile ci`
- `make -f tools/Makefile dev`
- `make -f tools/Makefile seed OFFERS=10 PRODUCTS=5`
- `make -f tools/Makefile seed-base`

## Python (Sail)
- `python3 tools/sail.py up|setup|reset|lint|phpstan|deptrac|test|test-all|ci`
- `python3 tools/sail.py seed --offers 10 --products 5` (or `python3 tools/sail.py seed-base`)
- `python3 tools/sail.py dev`

## Rust (Sail)
- `rustc tools/sail.rs -o tools/sail && tools/sail up|setup|reset|lint|phpstan|deptrac|test|test-all|ci`
- `tools/sail seed --offers 10 --products 5` (or `tools/sail seed-base`)
- `tools/sail dev`

## Local (no Docker)
- `sh tools/dev.sh --setup`
- `sh tools/dev.sh --reset`
- `sh tools/dev.sh --detach`
- `sh tools/dev.sh --stop`
- `sh tools/test-all.sh`
