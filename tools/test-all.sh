#!/usr/bin/env bash
set -euo pipefail

./vendor/bin/pint --test
./vendor/bin/phpstan analyse
./vendor/bin/deptrac analyse --config-file=deptrac.yaml
php artisan migrate
php artisan test
