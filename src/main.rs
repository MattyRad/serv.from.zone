mod txt;

use clap::{Parser, Subcommand};

pub mod cmd;

use human_panic::setup_panic;

use dotenvy::dotenv;
use std::path::PathBuf;
#[macro_use]
extern crate prettytable;

#[derive(Parser)]
#[command(author, version, about, long_about = None)]
struct Cli {
    /// Optional name to operate on
    name: Option<String>,

    /// Sets a custom config file
    #[arg(short, long, value_name = "FILE")]
    config: Option<PathBuf>,

    /// Turn debugging information on
    #[arg(short, long, action = clap::ArgAction::Count)]
    debug: u8,

    #[command(subcommand)]
    command: Option<Commands>,
}

#[derive(Subcommand)]
enum Commands {
    /// Starts the server
    Serve {
        /// The ip address to bind to
        #[arg(long, default_value = "0.0.0.0")]
        ip: String,

        /// The port to bind to
        #[arg(long, default_value_t = 80)]
        port: u16,
    },

    /// Process and show the records for a specific hostname
    Check {
        /// Check which records are detected
        #[arg(long)]
        hostname: String,
    },
}

#[actix_web::main]
async fn main() {
    setup_panic!();
    dotenv();

    let cli: Cli = Cli::parse();

    let _ = match &cli.command {
        Some(Commands::Serve { ip, port }) => cmd::serve(ip, *port).await,
        Some(Commands::Check { hostname }) => cmd::check(hostname.to_string()).await,
        None => Ok(()),
    };
}
