#!/usr/bin/env python3
import argparse
import subprocess
import sys
import time
from itertools import cycle
from pathlib import Path


ROOT = Path(__file__).resolve().parent.parent
SAIL = str(ROOT / "vendor/bin/sail")


def run(*args):
    command = [SAIL, *args]
    result = subprocess.run(command, check=False)
    if result.returncode != 0:
        sys.exit(result.returncode)


GREEN = "\033[32m"
BOLD_GREEN = "\033[1;32m"
RESET = "\033[0m"
ANIMALS = [
    ("Bunny", ["(\\_/)", "(o.o)", "/   \\"]),
    ("Cat", ["/\\_/\\", "( o.o )", "> ^ <"]),
    ("Dog", ["/^-----^\\", "V  o o  V", " \\  Y  /"]),
    ("Owl", [" ,_,", "(O,O)", "(   )"]),
]
ANIMAL_CYCLE = cycle(ANIMALS)
STEP_INDEX = 0


def print_step(label):
    global STEP_INDEX
    if STEP_INDEX > 0:
        print()
    STEP_INDEX += 1
    _name, lines = next(ANIMAL_CYCLE)
    print(f"{GREEN}Step: {label}{RESET}")
    for line in lines:
        print(f"{GREEN}{line}{RESET}")


def print_finished(elapsed):
    print(f"\nfinished in: {BOLD_GREEN}{elapsed:.2f}s{RESET}")


def step(label, *args):
    print_step(label)
    started = time.perf_counter()
    run(*args)
    elapsed = time.perf_counter() - started
    print_finished(elapsed)


def cmd_setup():
    step("composer install", "composer", "install")
    step("npm ci", "npm", "ci")
    step("artisan key:generate", "artisan", "key:generate")
    step("artisan migrate --seed", "artisan", "migrate", "--seed")
    step("artisan storage:link", "artisan", "storage:link")


def cmd_seed(offers, products):
    args = ["artisan", "demo:seed"]
    if offers is not None:
        args.extend(["--offers", str(offers)])
    if products is not None:
        args.extend(["--products", str(products)])
    step("artisan demo:seed", *args)


def cmd_ci():
    step("pint --test", "./vendor/bin/pint", "--test")
    step("phpstan analyse", "./vendor/bin/phpstan", "analyse")
    step("deptrac analyse", "./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml")
    step("artisan migrate", "artisan", "migrate")
    step("artisan test", "php", "artisan", "test")


def cmd_test():
    step("artisan migrate", "artisan", "migrate")
    step("artisan test", "php", "artisan", "test")


def cmd_dev():
    print_step("serve (background)")
    serve_start = time.perf_counter()
    serve = subprocess.Popen([SAIL, "php", "artisan", "serve"])
    try:
        step("queue:work", "php", "artisan", "queue:work")
    except SystemExit:
        raise
    except KeyboardInterrupt:
        pass
    finally:
        serve.terminate()
        serve.wait()
        elapsed = time.perf_counter() - serve_start
        print_finished(elapsed)


def main():
    parser = argparse.ArgumentParser(description="Sail helper")
    parser.add_argument(
        "command",
        choices=[
            "up",
            "down",
            "restart",
            "logs",
            "setup",
            "migrate",
            "reset",
            "seed",
            "seed-base",
            "dev",
            "test",
            "phpstan",
            "deptrac",
            "pint",
            "lint",
            "ci",
            "test-all",
        ],
    )
    parser.add_argument("--offers", type=int)
    parser.add_argument("--products", type=int)
    args = parser.parse_args()

    if args.command == "up":
        step("sail up -d", "up", "-d")
    elif args.command == "down":
        step("sail down", "down")
    elif args.command == "restart":
        step("sail down", "down")
        step("sail up -d", "up", "-d")
    elif args.command == "logs":
        step("sail logs -f", "logs", "-f")
    elif args.command == "setup":
        cmd_setup()
    elif args.command == "migrate":
        step("artisan migrate", "artisan", "migrate")
    elif args.command == "reset":
        step("artisan migrate:fresh --seed", "artisan", "migrate:fresh", "--seed")
    elif args.command == "seed":
        cmd_seed(args.offers, args.products)
    elif args.command == "seed-base":
        step("artisan db:seed", "artisan", "db:seed")
    elif args.command == "dev":
        cmd_dev()
    elif args.command == "test":
        cmd_test()
    elif args.command == "phpstan":
        step("phpstan analyse", "./vendor/bin/phpstan", "analyse")
    elif args.command == "deptrac":
        step("deptrac analyse", "./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml")
    elif args.command == "pint":
        step("pint", "./vendor/bin/pint")
    elif args.command == "lint":
        step("pint --test", "./vendor/bin/pint", "--test")
    elif args.command == "ci":
        cmd_ci()
    elif args.command == "test-all":
        cmd_ci()


if __name__ == "__main__":
    main()
