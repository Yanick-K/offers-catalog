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

print_log_snapshot() {
  label=$1
  file=$2
  if [ -s "$file" ]; then
    awk -v prefix="[$label] " '
      BEGIN { prev = "" }
      {
        if ($0 ~ /^[[:space:]]*$/) next;
        if ($0 == prev) next;
        prev = $0;
        print prefix $0;
      }
    ' "$file"
  fi
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

usage() {
  cat <<EOF
Usage: ./tools/dev.sh [--setup] [--reset] [--detach] [--stop]

Options:
  --setup   Run local setup steps before starting
  --reset   Reset the database (migrate:fresh --seed) before starting
  --detach  Start and exit without streaming logs
  --stop    Stop any running server/queue worker
  -h, --help  Show this help message
EOF
}

ROOT_DIR=$(cd "$(dirname "$0")/.." && pwd)
PID_DIR="$ROOT_DIR/storage/app/dev"
LOG_DIR="$ROOT_DIR/storage/logs"

SERVER_PID_FILE="$PID_DIR/server.pid"
QUEUE_PID_FILE="$PID_DIR/queue.pid"

SERVER_LOG="$LOG_DIR/dev-server.log"
QUEUE_LOG="$LOG_DIR/dev-queue.log"

mkdir -p "$PID_DIR" "$LOG_DIR"
cd "$ROOT_DIR"

DO_SETUP=false
DO_RESET=false
DETACH=false
STOP=false

for arg in "$@"; do
  case "$arg" in
    --setup)
      DO_SETUP=true
      ;;
    --reset)
      DO_RESET=true
      ;;
    --detach)
      DETACH=true
      ;;
    --stop)
      STOP=true
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      printf 'Unknown option: %s\n' "$arg" >&2
      usage >&2
      exit 1
      ;;
  esac
done

is_running() {
  pid=$1
  [ -n "${pid:-}" ] && kill -0 "$pid" 2>/dev/null
}

read_pid() {
  file=$1
  if [ -f "$file" ]; then
    cat "$file"
  fi
}

terminate_process() {
  pid=$1
  if [ -z "${pid:-}" ]; then
    return
  fi

  for child in $(ps -o pid= --ppid "$pid" 2>/dev/null); do
    terminate_process "$child"
  done

  kill -TERM "$pid" 2>/dev/null || true
}

force_kill_process() {
  pid=$1
  if [ -z "${pid:-}" ]; then
    return
  fi

  for child in $(ps -o pid= --ppid "$pid" 2>/dev/null); do
    force_kill_process "$child"
  done

  kill -KILL "$pid" 2>/dev/null || true
}

stop_if_exists() {
  file=$1
  name=$2
  pid=$(read_pid "$file" || true)

  if [ -n "${pid:-}" ] && is_running "$pid"; then
    print_step "stop $name"
    started=$(date +%s)
    terminate_process "$pid"

    i=0
    while [ "$i" -lt 10 ]; do
      if ! is_running "$pid"; then
        break
      fi
      sleep 1
      i=$((i + 1))
    done

    force_kill_process "$pid"

    ended=$(date +%s)
    print_finished "$((ended - started))"
  fi

  rm -f "$file"
}

cleanup() {
  exit_code=$?

  if [ -n "${TAIL_PID:-}" ]; then
    terminate_process "$TAIL_PID"
    wait "$TAIL_PID" 2>/dev/null || true
  fi

  if [ -n "${SERVER_PID:-}" ]; then
    terminate_process "$SERVER_PID"
    wait "$SERVER_PID" 2>/dev/null || true
  fi

  if [ -n "${QUEUE_PID:-}" ]; then
    terminate_process "$QUEUE_PID"
    wait "$QUEUE_PID" 2>/dev/null || true
  fi

  rm -f "$SERVER_PID_FILE" "$QUEUE_PID_FILE"

  exit "$exit_code"
}

if $STOP; then
  stop_if_exists "$SERVER_PID_FILE" "server"
  stop_if_exists "$QUEUE_PID_FILE" "queue worker"
  exit 0
fi

existing_server_pid=$(read_pid "$SERVER_PID_FILE" || true)
if [ -n "${existing_server_pid:-}" ] && is_running "$existing_server_pid"; then
  printf 'Server already running with PID %s\n' "$existing_server_pid" >&2
  exit 1
fi

existing_queue_pid=$(read_pid "$QUEUE_PID_FILE" || true)
if [ -n "${existing_queue_pid:-}" ] && is_running "$existing_queue_pid"; then
  printf 'Queue worker already running with PID %s\n' "$existing_queue_pid" >&2
  exit 1
fi

rm -f "$SERVER_PID_FILE" "$QUEUE_PID_FILE"

if $DO_SETUP; then
  if [ ! -f .env ] && [ -f .env.example ]; then
    run_step "copy .env.example" sh -c "cp .env.example .env"
  fi
  run_step "composer install" composer install
  run_step "npm ci" npm ci
  run_step "npm run build" npm run build
  run_step "artisan key:generate" php artisan key:generate
  if $DO_RESET; then
    run_step "artisan migrate:fresh --seed" php artisan migrate:fresh --seed
  else
    run_step "artisan migrate --seed" php artisan migrate --seed
  fi
  run_step "artisan storage:link" php artisan storage:link
elif $DO_RESET; then
  run_step "artisan migrate:fresh --seed" php artisan migrate:fresh --seed
fi

start_server() {
  : > "$SERVER_LOG"
  php artisan serve --no-reload >> "$SERVER_LOG" 2>&1 &
  SERVER_PID=$!
  printf '%s\n' "$SERVER_PID" > "$SERVER_PID_FILE"
  sleep 1
  if ! is_running "$SERVER_PID"; then
    printf 'Server failed to start. Check %s\n' "$SERVER_LOG" >&2
    return 1
  fi

  print_log_snapshot server "$SERVER_LOG"
}

start_queue() {
  : > "$QUEUE_LOG"
  php artisan queue:work >> "$QUEUE_LOG" 2>&1 &
  QUEUE_PID=$!
  printf '%s\n' "$QUEUE_PID" > "$QUEUE_PID_FILE"
  sleep 1
  if ! is_running "$QUEUE_PID"; then
    printf 'Queue worker failed to start. Check %s\n' "$QUEUE_LOG" >&2
    return 1
  fi

  print_log_snapshot queue "$QUEUE_LOG"
}

run_step "server" start_server
run_step "queue:work" start_queue

if $DETACH; then
  exit 0
fi

trap cleanup EXIT INT TERM

(tail -n 0 -F "$SERVER_LOG" "$QUEUE_LOG" 2>/dev/null | awk '
  BEGIN { current = "" }
  /^==> .*dev-server\.log <==$/ { current = "server"; next }
  /^==> .*dev-queue\.log <==$/  { current = "queue"; next }
  {
    if ($0 ~ /^[[:space:]]*$/) next;
    key = current != "" ? current : "default";
    if ($0 == prev[key]) next;
    prev[key] = $0;
    if (current == "server") print "[server] " $0;
    else if (current == "queue") print "[queue ] " $0;
    else print $0;
    fflush();
  }
') &
TAIL_PID=$!

while true; do
  if ! is_running "$SERVER_PID"; then
    printf '[dev] server stopped unexpectedly\n' >&2
    exit 1
  fi
  if ! is_running "$QUEUE_PID"; then
    printf '[dev] queue worker stopped unexpectedly\n' >&2
    exit 1
  fi
  sleep 1
done
