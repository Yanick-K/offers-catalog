use std::env;
use std::path::PathBuf;
use std::process::{Command, ExitStatus};
use std::sync::atomic::{AtomicUsize, Ordering};
use std::time::Instant;

const GREEN: &str = "\x1b[32m";
const BOLD_GREEN: &str = "\x1b[1;32m";
const RESET: &str = "\x1b[0m";
const ANIMALS: [(&str, [&str; 3]); 4] = [
    ("Bunny", ["(\\_/)", "(o.o)", "/   \\"]),
    ("Cat", ["/\\_/\\", "( o.o )", "> ^ <"]),
    ("Dog", ["/^-----^\\", "V  o o  V", " \\  Y  /"]),
    ("Owl", [" ,_,", "(O,O)", "(   )"]),
];
static STEP_INDEX: AtomicUsize = AtomicUsize::new(0);

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

fn print_step(label: &str) {
    let index = STEP_INDEX.fetch_add(1, Ordering::Relaxed);
    if index > 0 {
        println!();
    }
    let (_name, lines) = ANIMALS[index % ANIMALS.len()];
    println!("{GREEN}Step: {label}{RESET}");
    for line in lines {
        println!("{GREEN}{line}{RESET}");
    }
}

fn print_finished(elapsed: f64) {
    println!("\nfinished in: {BOLD_GREEN}{:.2}s{RESET}", elapsed);
}

fn run_step(label: &str, args: &[&str]) {
    print_step(label);
    let started = Instant::now();
    run_checked(args);
    let elapsed = started.elapsed().as_secs_f64();
    print_finished(elapsed);
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
    run_step("composer install", &["composer", "install"]);
    run_step("npm ci", &["npm", "ci"]);
    run_step("artisan key:generate", &["artisan", "key:generate"]);
    run_step("artisan migrate --seed", &["artisan", "migrate", "--seed"]);
    run_step("artisan storage:link", &["artisan", "storage:link"]);
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
    print_step("demo:seed");
    let started = Instant::now();
    run_checked_vec(&args);
    let elapsed = started.elapsed().as_secs_f64();
    print_finished(elapsed);
}

fn cmd_ci() {
    run_step("pint --test", &["./vendor/bin/pint", "--test"]);
    run_step("phpstan analyse", &["./vendor/bin/phpstan", "analyse"]);
    run_step(
        "deptrac analyse",
        &["./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml"],
    );
    run_step("artisan migrate", &["artisan", "migrate"]);
    run_step("artisan test", &["php", "artisan", "test"]);
}

fn cmd_test() {
    run_step("artisan migrate", &["artisan", "migrate"]);
    run_step("artisan test", &["php", "artisan", "test"]);
}

fn cmd_dev() {
    print_step("serve (background)");
    let serve_started = Instant::now();
    let mut serve = Command::new(sail_path())
        .args(["php", "artisan", "serve"])
        .spawn()
        .expect("failed to run serve");

    print_step("queue:work");
    let queue_started = Instant::now();
    let status = Command::new(sail_path())
        .args(["php", "artisan", "queue:work"])
        .status()
        .expect("failed to run queue");
    let queue_elapsed = queue_started.elapsed().as_secs_f64();
    print_finished(queue_elapsed);

    let _ = serve.kill();
    let _ = serve.wait();
    let serve_elapsed = serve_started.elapsed().as_secs_f64();
    print_finished(serve_elapsed);

    if !status.success() {
        std::process::exit(status.code().unwrap_or(1));
    }
}

fn usage() {
    eprintln!(
        "Usage: sail <up|down|restart|logs|setup|migrate|reset|seed|seed-base|dev|test|phpstan|deptrac|pint|lint|ci|test-all> [--offers N] [--products N]"
    );
}

fn main() {
    let args: Vec<String> = env::args().collect();
    if args.len() < 2 {
        usage();
        std::process::exit(1);
    }

    match args[1].as_str() {
        "up" => run_step("sail up -d", &["up", "-d"]),
        "down" => run_step("sail down", &["down"]),
        "restart" => {
            run_step("sail down", &["down"]);
            run_step("sail up -d", &["up", "-d"]);
        }
        "logs" => run_step("sail logs -f", &["logs", "-f"]),
        "setup" => cmd_setup(),
        "migrate" => run_step("artisan migrate", &["artisan", "migrate"]),
        "reset" => run_step(
            "artisan migrate:fresh --seed",
            &["artisan", "migrate:fresh", "--seed"],
        ),
        "seed" => cmd_seed(&args[2..]),
        "seed-base" => run_step("artisan db:seed", &["artisan", "db:seed"]),
        "dev" => cmd_dev(),
        "test" => cmd_test(),
        "phpstan" => run_step("phpstan analyse", &["./vendor/bin/phpstan", "analyse"]),
        "deptrac" => run_step(
            "deptrac analyse",
            &["./vendor/bin/deptrac", "analyse", "--config-file=deptrac.yaml"],
        ),
        "pint" => run_step("pint", &["./vendor/bin/pint"]),
        "lint" => run_step("pint --test", &["./vendor/bin/pint", "--test"]),
        "ci" => cmd_ci(),
        "test-all" => cmd_ci(),
        _ => {
            usage();
            std::process::exit(1);
        }
    }
}
