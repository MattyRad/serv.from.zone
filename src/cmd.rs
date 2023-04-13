use colored::*;

use actix_web::{get, App, HttpResponse, HttpServer, Responder};

use std::env;
use std::vec;
use trust_dns_proto::rr::record_type::RecordType;
use trust_dns_proto::rr::resource::Record;
use trust_dns_resolver::config::*;
use trust_dns_resolver::Resolver;

use crate::txt::html;

use prettytable::Table;

async fn get_records(hostname: String) -> Vec<String> {
    // https://stackoverflow.com/questions/62536566/how-can-i-create-a-tokio-runtime-inside-another-tokio-runtime-without-getting-th
    // https://stackoverflow.com/questions/52521201/how-do-i-synchronously-return-a-value-calculated-in-an-asynchronous-future
    // FIXME: Figure out how to use the built in AsyncResolver provided by Trust DNS
    tokio::task::spawn_blocking(|| {
        let resolver: Resolver =
            Resolver::new(ResolverConfig::default(), ResolverOpts::default()).unwrap();

        let response = match resolver.lookup(hostname, RecordType::TXT) {
            Ok(result) => result,
            Err(error) => {
                eprintln!("{error}");

                return vec![];
            }
        };

        let records: &[Record] = response.records();

        let mut txts = vec![];

        for record in records {
            let contents = record.data().unwrap().to_string();

            txts.push(html::reconstruct(contents));
        }

        txts
    })
    .await
    .unwrap()
}

#[get("/")]
async fn route_render_from_hostname(req: actix_web::HttpRequest) -> impl Responder {
    let hostname = match env::var("SERVER_HOSTNAME") {
        Ok(env_host) => env_host,
        Err(_) => req
            .headers()
            .get("Host")
            .unwrap()
            .to_str()
            .unwrap()
            .to_string(),
    };

    let document = html_document_from_host(hostname).await;

    HttpResponse::Ok().body(document.to_string())
}

async fn html_document_from_host(hostname: String) -> html::Document {
    let txts = get_records("html.".to_string() + &hostname).await;

    html::Document::from_txts(txts)
}

pub async fn serve(ip: &str, port: u16) -> std::io::Result<()> {
    std::env::set_var("RUST_LOG", "info");
    env_logger::init();

    HttpServer::new(|| App::new().service(route_render_from_hostname))
        .bind((ip, port))?
        .run()
        .await
}

pub async fn check(hostname: String) -> Result<(), std::io::Error> {
    let txts = get_records("html.".to_owned() + &hostname).await;

    let doc = html::Document::from_txts(txts);

    let mut head = Table::new();
    let mut body = Table::new();

    doc.head.into_iter().for_each(|record| {
        head.add_row(row![record.blue()]);
    });

    doc.body.into_iter().for_each(|record| {
        body.add_row(row![record.green()]);
    });

    println!("head elements");
    head.printstd();

    println!("body elements");
    body.printstd();

    Ok(())
}
