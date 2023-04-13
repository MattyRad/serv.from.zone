use std::fmt::Formatter;

use regex::Regex;

use base64::engine::general_purpose;
use base64::Engine;

use url::Url;

use std::path::Path;

pub const _TAGS: [&str; 134] = [
    "a",
    "abbr",
    "acronym",
    "address",
    "applet",
    "area",
    "article",
    "aside",
    "audio",
    "b",
    "base",
    "bdi",
    "bdo",
    "bgsound",
    "big",
    "blink",
    "blockquote",
    "body",
    "br",
    "button",
    "canvas",
    "caption",
    "center",
    "cite",
    "code",
    "col",
    "colgroup",
    "content",
    "data",
    "datalist",
    "dd",
    "del",
    "details",
    "dfn",
    "dialog",
    "dir",
    "div",
    "dl",
    "dt",
    "em",
    "embed",
    "fieldset",
    "figcaption",
    "figure",
    "font",
    "footer",
    "form",
    "frame",
    "frameset",
    "h1",
    "head",
    "header",
    "hgroup",
    "hr",
    "html",
    "i",
    "iframe",
    "image",
    "img",
    "input",
    "ins",
    "kbd",
    "keygen",
    "label",
    "legend",
    "li",
    "link",
    "main",
    "map",
    "mark",
    "marquee",
    "menu",
    "menuitem",
    "meta",
    "meter",
    "nav",
    "nobr",
    "noembed",
    "noframes",
    "noscript",
    "object",
    "ol",
    "optgroup",
    "option",
    "output",
    "p",
    "param",
    "picture",
    "plaintext",
    "portal",
    "pre",
    "progress",
    "q",
    "rb",
    "rp",
    "rt",
    "rtc",
    "ruby",
    "s",
    "samp",
    "script",
    "section",
    "select",
    "shadow",
    "slot",
    "small",
    "source",
    "spacer",
    "span",
    "strike",
    "strong",
    "style",
    "sub",
    "summary",
    "sup",
    "table",
    "tbody",
    "td",
    "template",
    "textarea",
    "tfoot",
    "th",
    "thead",
    "time",
    "title",
    "tr",
    "track",
    "tt",
    "u",
    "ul",
    "var",
    "video",
    "wbr",
    "xmp",
];

pub const TAGS_HEAD: [&str; 8] = [
    "title", "base", "link", "style", "meta", "script", "noscript", "template",
];

pub const _TAGS_VOID: [&str; 15] = [
    "area", "base", "br", "col", "embed", "hr", "img", "input", "keygen", "link", "meta", "param",
    "source", "track", "wbr",
];

#[derive(Default)]
pub struct Document {
    pub head: Vec<String>,
    pub body: Vec<String>,
}

impl std::fmt::Display for Document {
    fn fmt(&self, f: &mut Formatter) -> Result<(), std::fmt::Error> {
        write!(
            f,
            "<html><head>{}</head><body>{}</body></html>",
            self.head.to_html(),
            self.body.to_html()
        )
    }
}

fn is_head_element(s: String) -> bool {
    for tag in TAGS_HEAD {
        let html_tag = format!("<{tag}");
        let prefix = &s[0..html_tag.to_string().len()];

        if html_tag == prefix {
            return true;
        }
    }

    false
}

impl Document {
    pub fn from_txts(txts: Vec<String>) -> Self {
        Self {
            head: txts
                .clone()
                .into_iter()
                .filter(|s| is_head_element(s.to_string()))
                .collect(),
            body: txts
                .into_iter()
                .filter(|s| !is_head_element(s.to_string()))
                .collect(),
        }
    }
}

trait TxtToStr {
    fn to_html(&self) -> String;
}

impl TxtToStr for Vec<String> {
    fn to_html(&self) -> String {
        // TODO: Clean this all up, move ordering somewhere else
        let mut carry = String::new();

        let mut cloned = self.clone();

        cloned.sort();

        for txt in cloned {
            let mut split = txt.split('=');

            let stripped_order = match split.next() {
                Some(val) => {
                    let integer: Result<i32, _> = val.parse();

                    match integer {
                        Ok(_) => split.next().unwrap(),
                        Err(_) => &txt,
                    }
                }
                None => &txt,
            };

            carry += stripped_order.clone();
        }

        carry
    }
}

fn base64_decode(s: String) -> Result<String, &'static str> {
    let base64_candidate = general_purpose::STANDARD.decode(s);

    if base64_candidate.is_ok() {
        let s = std::str::from_utf8(&base64_candidate.unwrap())
            .unwrap()
            .to_string();

        return Ok(s);
    }

    Err("unable to decode")
}

fn reconstruct_base64(s: String) -> String {
    base64_decode(s.clone()).unwrap_or(s)
}

fn normalize_html_attrs(html: String) -> String {
    let re = Regex::new(r#"(\w+)\s*=\s*(\S+)"#).unwrap();
    let normalized_html = re.replace_all(html.as_str(), "$1=\"$2\"");

    normalized_html.to_string()
}

fn get_file_extension(url: Url) -> String {
    if let Some(mut segments) = url.path_segments() {
        if let Some(last_segment) = segments.next_back() {
            let path = Path::new(last_segment);

            if let Some(extension) = path.extension() {
                if let Some(extension_str) = extension.to_str() {
                    return extension_str.to_string();
                }
            }
        }
    }

    "".to_string()
}

fn reconstruct_url(s: String) -> String {
    let url = Url::parse(s.as_str());

    if url.is_err() {
        return s;
    }

    let extension_str = get_file_extension(url.unwrap());

    match extension_str.as_str() {
        "css" => format!("<link rel=\"stylesheet\" href=\"{s}\">"),
        "script" => format!("<script src=\"{s}\"></script>"),
        _ => "".to_string(),
    }
}

fn reconstruct_arrows(s: String) -> String {
    let normalized = normalize_html_attrs(s.clone());

    let void_prefix = &s[0..5];

    if ["link ", "meta ", "base "].contains(&void_prefix) {
        return format!("<{normalized}>");
    }

    if &s[0..7] == "script " {
        return format!("<{normalized}></script>");
    }

    // TODO: Other <head> tags

    s
}

pub fn reconstruct(s: String) -> String {
    reconstruct_base64(reconstruct_arrows(reconstruct_url(s)))
}
