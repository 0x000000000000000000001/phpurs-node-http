<?php

$exports = [];

if (!class_exists('PhpursHttpEmitter')) {
    class PhpursHttpEmitter {
        public $listeners = [];
        public $onceListeners = [];

        public function on($event, $cb) { $this->listeners[$event][] = $cb; return $this; }

        public function once($event, $cb) {
            $this->onceListeners[$event][] = $cb;
            return $this;
        }

        public function off($event, $cb) {
            foreach (['listeners', 'onceListeners'] as $slot) {
                if (isset($this->{$slot}[$event])) {
                    $this->{$slot}[$event] = array_values(array_filter($this->{$slot}[$event], function($c) use ($cb) { return $c !== $cb; }));
                }
            }
            return $this;
        }

        public function removeListener($event, $cb) { return $this->off($event, $cb); }

        public function emit($event, ...$args) {
            foreach (($this->listeners[$event] ?? []) as $cb) { $cb(...$args); }
            $once = $this->onceListeners[$event] ?? [];
            $this->onceListeners[$event] = [];
            foreach ($once as $cb) { $cb(...$args); }
            return true;
        }
    }
}

if (!class_exists('PhpursHttpMessage')) {
    class PhpursHttpMessage extends PhpursHttpEmitter {
        public $headers;
        public $rawHeaders = [];
        public $socket = null;

        public function __construct() {
            $this->headers = new \stdClass();
        }

        public function setHeader($key, $value) {
            $this->headers->{strtolower($key)} = $value;
        }

        public function appendHeader($key, $value) {
            $key = strtolower($key);
            $values = is_array($value) ? $value : [$value];
            if (property_exists($this->headers, $key)) {
                $current = is_array($this->headers->{$key}) ? $this->headers->{$key} : [$this->headers->{$key}];
                $this->headers->{$key} = array_merge($current, $values);
            } else {
                $this->headers->{$key} = $values;
            }
        }

        public function getHeader($key) {
            $key = strtolower($key);
            return property_exists($this->headers, $key) ? $this->headers->{$key} : null;
        }

        public function getHeaders() {
            return $this->headers;
        }

        public function getHeaderNames() { return array_keys(get_object_vars($this->headers)); }

        public function hasHeader($key) { return property_exists($this->headers, strtolower($key)); }

        public function removeHeader($key) { unset($this->headers->{strtolower($key)}); }

        public function addTrailers($headers) {
            foreach ((array) $headers as $key => $value) { $this->setHeader($key, $value); }
        }
    }
}

if (!class_exists('PhpursHttpIncomingMessage')) {
    class PhpursHttpIncomingMessage extends PhpursHttpMessage {
        public $url = '/';
        public $method = 'GET';
        public $httpVersion = '1.1';
        public $complete = true;
        public $statusCode = 200;
        public $statusMessage = 'OK';
        public $body = '';

        public function pipe($w) {
            if ($this->body !== '') {
                if (is_object($w) && method_exists($w, 'write')) {
                    $w->write($this->body);
                } elseif (is_object($w) && isset($w->write) && is_callable($w->write)) {
                    ($w->write)($this->body);
                }
            }
            if (is_object($w) && method_exists($w, 'end')) { $w->end(); }
            return $w;
        }

        public function read() { return null; }
        public function pause() {}
        public function resume() {}
        public function destroy() {}
    }
}

if (!class_exists('PhpursHttpOutgoingMessage')) {
    class PhpursHttpOutgoingMessage extends PhpursHttpMessage {
        public $sendHeaders = null;
        public $sendBody = null;
        public $sendEnd = null;

        public function write($data) {
            if ($this->sendBody !== null) { ($this->sendBody)($data); }
            return true;
        }

        public function end($data = null) {
            if ($data !== null) { $this->write($data); }
            if ($this->sendEnd !== null) { $cb = $this->sendEnd; $this->sendEnd = null; $cb(); }
            return $this;
        }

        public function flushHeaders() {}
    }
}

if (!class_exists('PhpursHttpServerResponse')) {
    class PhpursHttpServerResponse extends PhpursHttpOutgoingMessage {
        public $statusCode = 200;
        public $statusMessage = 'OK';
        public $sendDate = true;
        public $strictContentLength = false;
        public $req = null;
        public $responseBody = '';

        public function setStatusCode($code) { $this->statusCode = (int) $code; }

        public function writeHead($code, $headers = []) {
            $this->statusCode = (int) $code;
            foreach ((array) $headers as $key => $value) { $this->setHeader($key, $value); }
        }

        public function writeEarlyHints($hints) {}
        public function writeProcessing() {}
    }
}

if (!class_exists('PhpursHttpSocket')) {
    class PhpursHttpSocket extends PhpursHttpEmitter {
        public $writeSink = null;
        public $endSink = null;

        public function write($data) {
            if ($this->writeSink !== null) { ($this->writeSink)($data); }
            return true;
        }

        public function end($data = null) {
            if ($data !== null) { $this->write($data); }
            if ($this->endSink !== null) { $cb = $this->endSink; $this->endSink = null; $cb(); }
            return $this;
        }

        public function destroy() { $this->emit('close'); }
        public function pause() {}
        public function resume() {}
        public function read() { return null; }
    }
}

if (!class_exists('PhpursHttpServer')) {
    class PhpursHttpServer extends PhpursHttpEmitter {
        public static $registry = [];

        public $secure = false;
        public $headersTimeout = 60000;
        public $maxHeadersCount = 2000;
        public $requestTimeout = 300000;
        public $maxRequestsPerSocket = 0;
        public $timeout = 0;
        public $keepAliveTimeout = 5000;
        public $httpListen = null;

        public function __construct($secure = false) {
            $this->secure = $secure;
            $this->httpListen = function($options) { $this->doListen($options); };
        }

        public function doListen($options) {
            $port = (int) ($options->port ?? 0);
            self::$registry[$port] = $this;
            if (class_exists('\\Revolt\\EventLoop')) {
                \Revolt\EventLoop::queue(function() { $this->emit('listening'); });
            } else {
                $this->emit('listening');
            }
        }

        public function closeAllConnections() {}

        public function close() {
            foreach (self::$registry as $port => $server) {
                if ($server === $this) { unset(self::$registry[$port]); }
            }
            $this->emit('close');
        }
    }
}

if (!class_exists('PhpursHttpClientRequest')) {
    class PhpursHttpClientRequest extends PhpursHttpOutgoingMessage {
        public $path = '/';
        public $method = 'GET';
        public $host = 'localhost';
        public $protocol = 'http:';
        public $reusedSocket = false;
        public $options = null;
        public $started = false;
        public $bodyData = '';

        public function write($data) {
            $this->bodyData .= $data;
            if ($this->sendBody !== null) { ($this->sendBody)($data); }
            return true;
        }

        public function end($data = null) {
            if ($data !== null) { $this->write($data); }
            $this->start();
            return $this;
        }

        public function start() {
            if ($this->started) { return; }
            $this->started = true;
            PhpursHttpRuntime::perform($this);
        }
    }
}

if (!class_exists('PhpursHttpRuntime')) {
    class PhpursHttpRuntime {
        public static function parseUrl($url) {
            $parts = @parse_url($url);
            if ($parts === false) { return null; }
            return $parts;
        }

        public static function parseRequest($text) {
            $split = strpos($text, "\r\n\r\n");
            $head = $split === false ? $text : substr($text, 0, $split);
            $body = $split === false ? '' : substr($text, $split + 4);
            $lines = explode("\r\n", $head);
            $requestLine = array_shift($lines);
            $components = explode(' ', $requestLine);
            $method = $components[0] ?? 'GET';
            $url = $components[1] ?? '/';
            $httpVersion = isset($components[2]) ? str_replace('HTTP/', '', $components[2]) : '1.1';
            $headers = new \stdClass();
            $rawHeaders = [];
            foreach ($lines as $line) {
                if ($line === '') { continue; }
                $pos = strpos($line, ':');
                if ($pos === false) { continue; }
                $name = substr($line, 0, $pos);
                $value = ltrim(substr($line, $pos + 1));
                $rawHeaders[] = $name;
                $rawHeaders[] = $value;
                $key = strtolower($name);
                if ($key === 'set-cookie') {
                    $existing = property_exists($headers, $key) ? (array) $headers->{$key} : [];
                    $headers->{$key} = array_merge($existing, [$value]);
                } elseif (property_exists($headers, $key)) {
                    $headers->{$key} .= ', ' . $value;
                } else {
                    $headers->{$key} = $value;
                }
            }
            return ['method' => $method, 'url' => $url, 'httpVersion' => $httpVersion, 'headers' => $headers, 'rawHeaders' => $rawHeaders, 'body' => $body];
        }

        public static function parseResponse($text) {
            $split = strpos($text, "\r\n\r\n");
            $head = $split === false ? $text : substr($text, 0, $split);
            $body = $split === false ? '' : substr($text, $split + 4);
            $lines = explode("\r\n", $head);
            $statusLine = array_shift($lines);
            $status = 200;
            $statusMessage = 'OK';
            if (preg_match('/^HTTP\/[0-9.]+ ([0-9]+)(?: (.*))?$/', $statusLine, $m)) {
                $status = (int) $m[1];
                $statusMessage = $m[2] ?? '';
            }
            $headers = new \stdClass();
            foreach ($lines as $line) {
                $pos = strpos($line, ':');
                if ($pos === false) { continue; }
                $key = strtolower(substr($line, 0, $pos));
                $value = ltrim(substr($line, $pos + 1));
                if ($key === 'set-cookie') {
                    $existing = property_exists($headers, $key) ? (array) $headers->{$key} : [];
                    $headers->{$key} = array_merge($existing, [$value]);
                } elseif (property_exists($headers, $key)) {
                    $headers->{$key} .= ', ' . $value;
                } else {
                    $headers->{$key} = $value;
                }
            }
            return ['status' => $status, 'statusMessage' => $statusMessage, 'headers' => $headers, 'body' => $body];
        }

        public static function makeRequestMessage($parsed, $socket) {
            $req = new PhpursHttpIncomingMessage();
            $req->url = $parsed['url'];
            $req->method = $parsed['method'];
            $req->httpVersion = $parsed['httpVersion'];
            $req->headers = $parsed['headers'];
            $req->rawHeaders = $parsed['rawHeaders'];
            $req->body = $parsed['body'];
            $req->socket = $socket;
            return $req;
        }

        public static function makeResponseMessage($parsed) {
            $res = new PhpursHttpIncomingMessage();
            $res->statusCode = $parsed['status'];
            $res->statusMessage = $parsed['statusMessage'];
            $res->headers = $parsed['headers'];
            $res->body = $parsed['body'];
            return $res;
        }

        public static function isUpgrade($headers) {
            $connection = strtolower($headers->connection ?? '');
            return isset($headers->upgrade) && strpos($connection, 'upgrade') !== false;
        }

        public static function buildResponseText($res) {
            $status = $res->statusCode;
            $message = $res->statusMessage !== '' ? $res->statusMessage : 'OK';
            $body = '';
            // The handler may share a buffer through sendBody; the caller passes it.
            $text = "HTTP/1.1 $status $message\r\n";
            $hasLength = false;
            foreach (get_object_vars($res->headers) as $key => $value) {
                if (strtolower($key) === 'content-length') { $hasLength = true; }
                if (is_array($value)) {
                    foreach ($value as $item) { $text .= "$key: $item\r\n"; }
                } else {
                    $text .= "$key: $value\r\n";
                }
            }
            if (!$hasLength) { $text .= "Content-Length: " . strlen($res->responseBody) . "\r\n"; }
            $text .= "Connection: close\r\n\r\n";
            $text .= $res->responseBody;
            return $text;
        }

        public static function dispatch($server, $requestText) {
            $parsed = self::parseRequest($requestText);
            $socket = new PhpursHttpSocket();
            $req = self::makeRequestMessage($parsed, $socket);

            if (self::isUpgrade($parsed['headers'])) {
                $buffer = '';
                $socket->writeSink = function($data) use (&$buffer) { $buffer .= $data; };
                $server->emit('upgrade', $req, $socket, '');
                return $buffer;
            }

            $res = new PhpursHttpServerResponse();
            $res->req = $req;
            $res->responseBody = '';
            $res->sendBody = function($data) use ($res) { $res->responseBody .= $data; };
            $server->emit('request', $req, $res);
            return self::buildResponseText($res);
        }

        public static function buildRequestText($req) {
            $opts = $req->options ?? new \stdClass();
            $protocol = $opts->protocol ?? $req->protocol;
            $host = $opts->hostname ?? $opts->host ?? $req->host;
            $port = (int) ($opts->port ?? ($protocol === 'https:' ? 443 : 80));
            $path = $opts->path ?? $req->path;
            $method = $opts->method ?? $req->method;
            $defaultPort = ($protocol === 'https:' ? 443 : 80);
            $hostHeader = $port === $defaultPort ? $host : "$host:$port";
            $text = "$method $path HTTP/1.1\r\nHost: $hostHeader\r\n";
            if (!property_exists($req->headers, 'connection')) {
                $text .= "Connection: close\r\n";
            }
            foreach (get_object_vars($req->headers) as $key => $value) {
                if (strtolower($key) === 'host') { continue; }
                if (is_array($value)) {
                    foreach ($value as $item) { $text .= "$key: $item\r\n"; }
                } else {
                    $text .= "$key: $value\r\n";
                }
            }
            if ($req->bodyData !== '') {
                $text .= "Content-Length: " . strlen($req->bodyData) . "\r\n";
            }
            $text .= "\r\n" . $req->bodyData;
            return $text;
        }

        public static function perform($req) {
            $opts = $req->options ?? new \stdClass();
            $protocol = $opts->protocol ?? $req->protocol;
            $host = $opts->hostname ?? $opts->host ?? $req->host;
            $port = (int) ($opts->port ?? ($protocol === 'https:' ? 443 : 80));
            $text = self::buildRequestText($req);

            $local = null;
            if (($host === 'localhost' || $host === '127.0.0.1') && isset(PhpursHttpServer::$registry[$port])) {
                $local = PhpursHttpServer::$registry[$port];
            }

            if ($local !== null) {
                $responseText = self::dispatch($local, $text);
            } else {
                $responseText = self::remoteRequest($text, $protocol, $host, $port, $opts);
                if ($responseText === null) { return; }
            }

            $parsedResponse = self::parseResponse($responseText);
            $response = self::makeResponseMessage($parsedResponse);
            if ($parsedResponse['status'] === 101) {
                $response->socket = new PhpursHttpSocket();
            }

            if (class_exists('\\Revolt\\EventLoop')) {
                \Revolt\EventLoop::queue(function() use ($req, $response) { $req->emit('response', $response); });
            } else {
                $req->emit('response', $response);
            }
        }

        public static function remoteRequest($text, $protocol, $host, $port, $opts) {
            $secure = $protocol === 'https:';
            $ssl = [];
            if (isset($opts->rejectUnauthorized) && $opts->rejectUnauthorized === false) {
                $ssl = ['verify_peer' => false, 'verify_peer_name' => false];
            }
            $context = stream_context_create($ssl === [] ? [] : ['ssl' => $ssl]);
            $remote = ($secure ? 'ssl://' : 'tcp://') . $host . ':' . $port;
            $client = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
            if ($client === false) {
                fwrite(STDERR, "HTTP request to $remote failed: $errstr\n");
                return null;
            }
            fwrite($client, $text);
            $responseText = stream_get_contents($client);
            fclose($client);
            return $responseText === false ? null : $responseText;
        }

        public static function requestFromUrl($url, $opts = null) {
            $parts = self::parseUrl($url);
            $base = $opts === null ? new \stdClass() : $opts;
            if (!isset($base->protocol)) { $base->protocol = ($parts['scheme'] ?? 'http') . ':'; }
            if (!isset($base->hostname) && !isset($base->host)) { $base->hostname = $parts['host'] ?? 'localhost'; }
            if (!isset($base->port)) { $base->port = $parts['port'] ?? ($base->protocol === 'https:' ? 443 : 80); }
            if (!isset($base->path)) { $base->path = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : ''); }
            return self::makeRequest($base);
        }

        public static function makeRequest($opts) {
            $req = new PhpursHttpClientRequest();
            $req->options = $opts;
            $req->protocol = $opts->protocol ?? 'http:';
            $req->host = $opts->hostname ?? $opts->host ?? 'localhost';
            $req->path = $opts->path ?? '/';
            $req->method = $opts->method ?? 'GET';
            if (isset($opts->headers)) {
                foreach ((array) $opts->headers as $key => $value) { $req->setHeader($key, $value); }
            }
            return $req;
        }
    }
}

$exports['maxHeaderSize'] = 16384;

$exports['createServer'] = function() { return new PhpursHttpServer(false); };

$exports['createServerOptsImpl'] = function($opts) { return new PhpursHttpServer(false); };

$exports['requestStrImpl'] = function($url) {
    return PhpursHttpRuntime::requestFromUrl($url);
};

$exports['requestUrlImpl'] = function($url) {
    return PhpursHttpRuntime::requestFromUrl($url);
};

$exports['requestStrOptsImpl'] = function($url, $opts) {
    return PhpursHttpRuntime::requestFromUrl($url, $opts);
};

$exports['requestUrlOptsImpl'] = function($url, $opts) {
    return PhpursHttpRuntime::requestFromUrl($url, $opts);
};

$exports['requestOptsImpl'] = function($opts) {
    return PhpursHttpRuntime::makeRequest($opts);
};

$exports['getStrImpl'] = function($url) {
    $req = PhpursHttpRuntime::requestFromUrl($url);
    $req->end();
    return $req;
};

$exports['getUrlImpl'] = function($url) {
    $req = PhpursHttpRuntime::requestFromUrl($url);
    $req->end();
    return $req;
};

$exports['getStrOptsImpl'] = function($url, $opts) {
    $req = PhpursHttpRuntime::requestFromUrl($url, $opts);
    $req->end();
    return $req;
};

$exports['getUrlOptsImpl'] = function($url, $opts) {
    $req = PhpursHttpRuntime::requestFromUrl($url, $opts);
    $req->end();
    return $req;
};

$exports['getOptsImpl'] = function($opts) {
    $req = PhpursHttpRuntime::makeRequest($opts);
    $req->end();
    return $req;
};

$exports['setMaxIdleHttpParsersImpl'] = function($n) {};

return $exports;
