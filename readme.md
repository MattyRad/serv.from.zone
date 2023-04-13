# serv.from.zone

Serve a website from TXT records.

## Setup

CNAME or ALIAS your root hostname to point to `serv.from.zone`.

Then you can has `html` hostnames containing HTML which will be served.

```
dig example.com
```

```dig
;; ANSWER SECTION:
example.com.	99	IN	CNAME	serv.from.zone.
```

```
dig TXT html.example.com
```

```dig
;; ANSWER SECTION:
html.example.com. 99	IN	TXT	"<p>Hello world!</p>"
```

### Head

#### CSS/JS Urls

JS and CSS urls can be dropped in to the TXT record and it will be included in the `<head>` element.

```
https://cdn.jsdelivr.net/npm/water.css@2/out/dark.css
```

#### Elements

It's possible to supply raw HTML, assuming your DNS provider doesn't block suspicious input:

```
<script type=module src=https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.3.0/dist/shoelace-autoloader.js></script>
```

But if your DNS provider doesn't allow that, then you can remove the arrows and it will get reconstructed:

```
script type=module src=https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.3.0/dist/shoelace-autoloader.js
```

```
meta attr=someattr
```

### Body

Everything that's not specifically a `head` element gets added as the body.

```
<p>Hello World</p>
```

### Base64 Encoding

If your DNS provider blocks the HTML you want to store in your TXT record, you can base64 encode the contents.

```
echo "<p>Let's run a script in the body\!</p><script>alert('I cannot be stopped\!')</script>" | base64 --wrap=0
```

```
PHA+TGV0J3MgcnVuIGEgc2NyaXB0IGluIHRoZSBib2R5ITwvcD48c2NyaXB0PmFsZXJ0KCdJIGNhbm5vdCBiZSBzdG9wcGVkIScpPC9zY3JpcHQ+Cg==
```

Base64 encoding may also help with unexpected quoting (`"`, `'`) problems.

### Ordering

DNS providers and TXT records have length limits, so you may want to split up your body HTML into multiple records. You can specify the order TXT records should appear in using an integer prefix.

```
1=<h1>First</h1>
```

```
2=<h2>Second</h2>
```

Base64 decoding happens first, so if you use base64 be sure to include the `i=` prefix in the encoded string.

## What for?

 - It's kinda neat.
 - It's free hosting.
 - No signup, login, or personal data required.
 - You can get a simple website running from the comfort of your domain registrar's admin panel.
 - Reduce the need for dedicated machines by serving many sites from 1 generalized server.
 - Attaching domain specific content to the zone makes sense in some cases ([RFC](https://tools.ietf.org/html/rfc1464#section-2)), since the data is decoupled from a specific [web] server. In this case, a string which represents HTML.

This website is dogfooded!

```
dig +short TXT html.serv.from.zone
```
