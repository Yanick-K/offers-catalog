use std::env;
use std::path::PathBuf;
use std::process::{Command, ExitStatus};

fn sail_path() -> String {
    let exe = env::current_exe().expect("failed to resolve current exe");
    let root = exe
        .parent()
        .and_then(|path| path.parent())
        .map(PathBuf::from)
        .expect("failed to resolve project root");
    root.join("vendor/bin/sail").to_string_lossy().into_owned()
}

fn run(args: &[&str]) -> ExitStatus {
    Command::new(sail_path())
        .args(args)
        .status()
        .expect("failed to run sail")
}

fn run_checked(args: &[&str]) {
    let status = run(args);
    if !status.success() {
        std::process::exit(status.code().unwrap_or(1));
    }
}

fn run_checked_vec(args: &[String]) {
    let status = Command::new(sail_path())
        .args(args)
        .status()
        .expect("failed to run sail");
    if !status.success() {
        std::process::exit(status.code().unwrap_or(1));
    }
}

fn cmd_setup() {
    run_checked(&["composer", "install"]);
    run_checked(&["npm", "ci"]);
    run_checked(&["artisan", "key:generate"]);
    run_checked(&["artisan", "migrate", "--seed"]);
    run_checked(&["artisan", "storage:link"]);
}

fn cmd_seed(extra: &[String]) {
    let mut offers: Option<String> = None;
    let mut products: Option<String> = None;
    let mut i = 0;
    while i < extra.len() {
        match extra[i].as_str() {
            "--offers" => {
                i += 1;
                offers = extra.get(i).cloned();
            }
            "--products" => {
                i += 1;
                products = extra.get(i).cloned();
            }
            _ => {
                eprintln!("Unknown option: {}", extra[i]);
                usage();
                std::process::exit(1);
            }
        }
        i += 1;
    }

    let mut args = vec!["artisan".to_string(), "demo:seed".to_string()];
    if let Some(value) = offers {
        args.push("--offers".to_string());
        args.push(value);
    }
    if let Some(value) = products {
        args.push("--products".to_string());
        args.push(value);
    }
    run_checked_vec(&args);
}

fn cmd_ci() {
    run_checked(&["./vendor/bin/pint", "--test"]);
    run_checked(&["./vendor/bin/phpstan", "analyse"]);
    run_checked(&["./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml"]);
    run_checked(&["artisan", "migrate"]);
    run_checked(&["php", "artisan", "test"]);
}

fn cmd_test() {
    run_checked(&["artisan", "migrate"]);
    run_checked(&["php", "artisan", "test"]);
}

fn cmd_dev() {
    let mut serve = Command::new(sail_path())
        .args(["php", "artisan", "serve"])
        .spawn()
        .expect("failed to run serve");

    let status = Command::new(sail_path())
        .args(["php", "artisan", "queue:work"])
        .status()
        .expect("failed to run queue");

    let _ = serve.kill();
    let _ = serve.wait();

    if !status.success() {
        std::process::exit(status.code().unwrap_or(1));
    }
}

fn usage() {
    eprintln!(
        "Usage: sail <up|down|restart|logs|setup|migrate|seed|seed-base|dev|test|phpstan|deptrac|pint|lint|ci|test-all> [--offers N] [--products N]"
    );
}

fn main() {
    let args: Vec<String> = env::args().collect();
    if args.len() < 2 {
        usage();
        std::process::exit(1);
    }

    match args[1].as_str() {
        "up" => run_checked(&["up", "-d"]),
        "down" => run_checked(&["down"]),
        "restart" => {
            run_checked(&["down"]);
            run_checked(&["up", "-d"]);
        }
        "logs" => run_checked(&["logs", "-f"]),
        "setup" => cmd_setup(),
        "migrate" => run_checked(&["artisan", "migrate"]),
        "seed" => cmd_seed(&args[2..]),
        "seed-base" => run_checked(&["artisan", "db:seed"]),
        "dev" => cmd_dev(),
        "test" => cmd_test(),
        "phpstan" => run_checked(&["./vendor/bin/phpstan", "analyse"]),
        "deptrac" => run_checked(&["./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml"]),
        "pint" => run_checked(&["./vendor/bin/pint"]),
        "lint" => run_checked(&["./vendor/bin/pint", "--test"]),
        "ci" => cmd_ci(),
        "test-all" => cmd_ci(),
        _ => {
            usage();
            std::process::exit(1);
        }
    }
}
