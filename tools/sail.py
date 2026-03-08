#!/usr/bin/env python3
import argparse
import subprocess
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parent.parent
SAIL = str(ROOT / "vendor/bin/sail")


def run(*args):
    command = [SAIL, *args]
    result = subprocess.run(command, check=False)
    if result.returncode != 0:
        sys.exit(result.returncode)


def cmd_setup():
    run("composer", "install")
    run("npm", "ci")
    run("artisan", "key:generate")
    run("artisan", "migrate", "--seed")
    run("artisan", "storage:link")


def cmd_seed(offers, products):
    args = ["artisan", "demo:seed"]
    if offers is not None:
        args.extend(["--offers", str(offers)])
    if products is not None:
        args.extend(["--products", str(products)])
    run(*args)


def cmd_ci():
    run("./vendor/bin/pint", "--test")
    run("./vendor/bin/phpstan", "analyse")
    run("./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml")
    run("artisan", "migrate")
    run("php", "artisan", "test")


def cmd_test():
    run("artisan", "migrate")
    run("php", "artisan", "test")


def cmd_dev():
    serve = subprocess.Popen([SAIL, "php", "artisan", "serve"])
    try:
        run("php", "artisan", "queue:work")
    except KeyboardInterrupt:
        pass
    finally:
        serve.terminate()
        serve.wait()


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
        run("up", "-d")
    elif args.command == "down":
        run("down")
    elif args.command == "restart":
        run("down")
        run("up", "-d")
    elif args.command == "logs":
        run("logs", "-f")
    elif args.command == "setup":
        cmd_setup()
    elif args.command == "migrate":
        run("artisan", "migrate")
    elif args.command == "seed":
        cmd_seed(args.offers, args.products)
    elif args.command == "seed-base":
        run("artisan", "db:seed")
    elif args.command == "dev":
        cmd_dev()
    elif args.command == "test":
        cmd_test()
    elif args.command == "phpstan":
        run("./vendor/bin/phpstan", "analyse")
    elif args.command == "deptrac":
        run("./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml")
    elif args.command == "pint":
        run("./vendor/bin/pint")
    elif args.command == "lint":
        run("./vendor/bin/pint", "--test")
    elif args.command == "ci":
        cmd_ci()
    elif args.command == "test-all":
        cmd_ci()


if __name__ == "__main__":
    main()
