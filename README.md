# phpurs-node-http

> PHPurs checkout: see the [PHP library development guide](https://github.com/0x000000000000000000001/phpurs/blob/main/docs/libraries.md) for the TAST toolchain, FFI conventions and validation commands. Run `./bin/test` from this package with the PHP toolchain prepared.

[![Latest release](http://img.shields.io/github/release/purescript-node/purescript-node-http.svg)](https://github.com/purescript/purescript-node-http/releases)
[![Build status](https://github.com/purescript-node/purescript-node-http/workflows/CI/badge.svg?branch=master)](https://github.com/purescript-node/purescript-node-http/actions?query=workflow%3ACI+branch%3Amaster)
[![Pursuit](https://pursuit.purescript.org/packages/purescript-node-http/badge)](https://pursuit.purescript.org/packages/purescript-node-http)

A wrapper for Node's [HTTP](https://nodejs.org/dist/latest-v18.x/docs/api/http.html) and [HTTPS](https://nodejs.org/dist/latest-v18.x/docs/api/https.html) APIs.

## PHP runtime

The PHP implementation is a compatibility layer for these bindings. Its runtime
lives in `src/Node/HTTP.php`; the adjacent HTTP modules expose individual message,
request, and response operations. Keep runtime helpers inside their FFI module:
phpurs embeds this source into generated PHP.

Servers register by port inside the current process. Requests to `localhost` or
`127.0.0.1` dispatch through that registry; remote requests use blocking PHP TCP
or TLS sockets. The server constructors do not open network listeners. Messages
are buffered, and timeout settings and several streaming methods are placeholders.
The upstream API documentation describes the bindings, not complete PHP parity.

## Installation

```
spago install node-http
```

## Documentation

Module documentation is [published on Pursuit](http://pursuit.purescript.org/packages/purescript-node-http).
