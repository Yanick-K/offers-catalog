#!/usr/bin/env bash
set -euo pipefail

php artisan serve &
serve_pid=$!

cleanup() {
  kill "$serve_pid" 2>/dev/null || true
}

trap cleanup EXIT

php artisan queue:work
