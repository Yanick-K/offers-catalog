#!/usr/bin/env sh
set -eu

GREEN=$(printf '\033[32m')
BOLD_GREEN=$(printf '\033[1;32m')
RESET=$(printf '\033[0m')
STEP_INDEX=0

print_step() {
  label=$1
  if [ "$STEP_INDEX" -gt 0 ]; then
    printf '\n'
  fi
  STEP_INDEX=$((STEP_INDEX + 1))
  case $((STEP_INDEX % 4)) in
    1)
      line1='(\_/)'
      line2='(o.o)'
      line3='/   \'
      ;;
    2)
      line1='/\_/\'
      line2='( o.o )'
      line3='> ^ <'
      ;;
    3)
      line1='/^-----^\'
      line2='V  o o  V'
      line3=' \  Y  /'
      ;;
    0)
      line1=' ,_,'
      line2='(O,O)'
      line3='(   )'
      ;;
  esac
  printf '%sStep: %s%s\n' "$GREEN" "$label" "$RESET"
  printf '%s%s%s\n' "$GREEN" "$line1" "$RESET"
  printf '%s%s%s\n' "$GREEN" "$line2" "$RESET"
  printf '%s%s%s\n' "$GREEN" "$line3" "$RESET"
}

print_finished() {
  elapsed=$1
  printf '\nfinished in: %s%ss%s\n' "$BOLD_GREEN" "$elapsed" "$RESET"
}

run_step() {
  label=$1
  shift
  print_step "$label"
  started=$(date +%s)
  "$@"
  ended=$(date +%s)
  print_finished "$((ended - started))"
}

run_step "pint --test" ./vendor/bin/pint --test
run_step "phpstan analyse" ./vendor/bin/phpstan analyse
run_step "deptrac analyse" ./vendor/bin/deptrac analyse --config-file=deptrac.yaml
run_step "artisan migrate" php artisan migrate
run_step "artisan test" php artisan test
