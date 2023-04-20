# serv.from.zone

Serve a website from TXT records.

## How

CNAME or ALIAS your root hostname to point to `serv.from.zone`.

Then you can add `html` hostnames containing HTML, which will be served.

```
example.com.      3600  IN  CNAME serv.from.zone.
html.example.com. 3600  IN  TXT   "<p>Hello world!</p>"
```

## Hacking around TXT records

### CSS/JS Urls

JS and CSS urls can be dropped in to the TXT record and they will be included in the `<head>` element.

```
https://cdn.jsdelivr.net/npm/water.css@2/out/dark.css
```

### Elements

It's possible to supply raw HTML, assuming your DNS provider allows suspicious input:

```html
<script type=module src=https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.3.0/dist/shoelace-autoloader.js></script>
```

But if your DNS provider doesn't allow suspicous input, then you can remove the arrows and it will get reconstructed:

```
script type=module src=https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.3.0/dist/shoelace-autoloader.js
```






```
meta name=theme-color content=#4285f4
```

Everything that's not detected as a `<head>` element gets added as the body.

### Base64 Encoding

You can make your input less suspicious by base64 encoding it.

```
echo "<p>Let's run a script in the body\!</p><script>alert('I cannot be stopped\!')</script>" | base64 --wrap=0
```

```
PHA+TGV0J3MgcnVuIGEgc2NyaXB0IGluIHRoZSBib2R5ITwvcD48c2NyaXB0PmFsZXJ0KCdJIGNhbm5vdCBiZSBzdG9wcGVkIScpPC9zY3JpcHQ+Cg==
```

Base64 encoding may also help with unexpected quoting (`"`, `'`) problems.

### Ordering

You may want to split up your body HTML into multiple TXT records. You can specify the order TXT records should appear in using an integer prefix.

`1=<h1>First</h1>`

`2=<h2>Second</h2>`

Base64 decoding happens prior to ordering, so if you use base64, be sure to include the `i=` prefix in the encoded string.

## What for?

 - It's kinda neat.
 - It's free hosting.
 - No signup, login, or personal data required.
 - You can get a simple website running from the comfort of your domain registrar's admin panel.
 - Reduce the need for dedicated machines by serving many sites from 1 generalized server.

serv.from.zone is dogfooded!

```
dig +short TXT html.serv.from.zone
```
