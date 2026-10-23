<?php

$exports = [];

// Runtime classes stay in this FFI module: phpurs embeds its contents in the
// generated module. Local servers use the registry; remote requests use sockets.

if (!class_exists('PhpursHttpEmitter')) {
    class PhpursHttpEmitter
    {
        public $listeners = [];
        public $onceListeners = [];

        public function on($event, $callback)
        {
            $this->listeners[$event][] = $callback;
            return $this;
        }

        public function once($event, $callback)
        {
            $this->onceListeners[$event][] = $callback;
            return $this;
        }

        public function off($event, $callback)
        {
            foreach (['listeners', 'onceListeners'] as $slot) {
                if (isset($this->{$slot}[$event])) {
                    $this->{$slot}[$event] = array_values(
                        array_filter($this->{$slot}[$event], function ($listener) use ($callback) {
                            return $listener !== $callback;
                        }),
                    );
                }
            }
            return $this;
        }

        public function removeListener($event, $callback)
        {
            return $this->off($event, $callback);
        }

        public function emit($event, ...$args)
        {
            foreach ($this->listeners[$event] ?? [] as $callback) {
                $callback(...$args);
            }
            $once = $this->onceListeners[$event] ?? [];
            $this->onceListeners[$event] = [];
            foreach ($once as $callback) {
                $callback(...$args);
            }
            return true;
        }
    }
}

if (!class_exists('PhpursHttpMessage')) {
    class PhpursHttpMessage extends PhpursHttpEmitter
    {
        public $headers;
        public $rawHeaders = [];
        public $socket = null;

        public function __construct()
        {
            $this->headers = new \stdClass();
        }

        public function setHeader($key, $value)
        {
            $this->headers->{strtolower($key)} = $value;
        }

        public function appendHeader($key, $value)
        {
            $key = strtolower($key);
            $values = is_array($value) ? $value : [$value];
            if (property_exists($this->headers, $key)) {
                $current = is_array($this->headers->{$key})
                    ? $this->headers->{$key}
                    : [$this->headers->{$key}];
                $this->headers->{$key} = array_merge($current, $values);
            } else {
                $this->headers->{$key} = $values;
            }
        }

        public function getHeader($key)
        {
            $key = strtolower($key);
            return property_exists($this->headers, $key) ? $this->headers->{$key} : null;
        }

        public function getHeaders()
        {
            return $this->headers;
        }

        public function getHeaderNames()
        {
            return array_keys(get_object_vars($this->headers));
        }

        public function hasHeader($key)
        {
            return property_exists($this->headers, strtolower($key));
        }

        public function removeHeader($key)
        {
            unset($this->headers->{strtolower($key)});
        }

        public function addTrailers($headers)
        {
            foreach ((array) $headers as $key => $value) {
                $this->setHeader($key, $value);
            }
        }
    }
}

if (!class_exists('PhpursHttpIncomingMessage')) {
    class PhpursHttpIncomingMessage extends PhpursHttpMessage
    {
        public $url = '/';
        public $method = 'GET';
        public $httpVersion = '1.1';
        public $complete = true;
        public $statusCode = 200;
        public $statusMessage = 'OK';
        public $body = '';

        public function pipe($destination)
        {
            if ($this->body !== '') {
                if (is_object($destination) && method_exists($destination, 'write')) {
                    $destination->write($this->body);
                } elseif (
                    is_object($destination) &&
                    isset($destination->write) &&
                    is_callable($destination->write)
                ) {
                    ($destination->write)($this->body);
                }
            }
            if (is_object($destination) && method_exists($destination, 'end')) {
                $destination->end();
            }
            return $destination;
        }

        public function read()
        {
            return null;
        }
        public function pause() {}
        public function resume() {}
        public function destroy() {}
    }
}

if (!class_exists('PhpursHttpOutgoingMessage')) {
    class PhpursHttpOutgoingMessage extends PhpursHttpMessage
    {
        public $sendBody = null;
        public $sendEnd = null;

        public function write($data)
        {
            if ($this->sendBody !== null) {
                ($this->sendBody)($data);
            }
            return true;
        }

        public function end($data = null)
        {
            if ($data !== null) {
                $this->write($data);
            }
            if ($this->sendEnd !== null) {
                $callback = $this->sendEnd;
                $this->sendEnd = null;
                $callback();
            }
            return $this;
        }

        public function flushHeaders() {}
    }
}

if (!class_exists('PhpursHttpServerResponse')) {
    class PhpursHttpServerResponse extends PhpursHttpOutgoingMessage
    {
        public $statusCode = 200;
        public $statusMessage = 'OK';
        public $sendDate = true;
        public $strictContentLength = false;
        public $req = null;
        public $responseBody = '';

        public function setStatusCode($code)
        {
            $this->statusCode = (int) $code;
        }

        public function writeHead($code, $headers = [])
        {
            $this->statusCode = (int) $code;
            foreach ((array) $headers as $key => $value) {
                $this->setHeader($key, $value);
            }
        }

        public function writeEarlyHints($hints) {}
        public function writeProcessing() {}
    }
}

if (!class_exists('PhpursHttpSocket')) {
    class PhpursHttpSocket extends PhpursHttpEmitter
    {
        public $writeSink = null;
        public $endSink = null;

        public function write($data)
        {
            if ($this->writeSink !== null) {
                ($this->writeSink)($data);
            }
            return true;
        }

        public function end($data = null)
        {
            if ($data !== null) {
                $this->write($data);
            }
            if ($this->endSink !== null) {
                $callback = $this->endSink;
                $this->endSink = null;
                $callback();
            }
            return $this;
        }

        public function destroy()
        {
            $this->emit('close');
        }
        public function pause() {}
        public function resume() {}
        public function read()
        {
            return null;
        }
    }
}

if (!class_exists('PhpursHttpServer')) {
    class PhpursHttpServer extends PhpursHttpEmitter
    {
        public static $registry = [];

        public $secure = false;
        public $headersTimeout = 60000;
        public $maxHeadersCount = 2000;
        public $requestTimeout = 300000;
        public $maxRequestsPerSocket = 0;
        public $timeout = 0;
        public $keepAliveTimeout = 5000;
        public $httpListen = null;

        public function __construct($secure = false)
        {
            $this->secure = $secure;
            $this->httpListen = function ($options) {
                $this->doListen($options);
            };
        }

        public function doListen($options)
        {
            $port = (int) ($options->port ?? 0);
            self::$registry[$port] = $this;
            if (class_exists('\\Revolt\\EventLoop')) {
                \Revolt\EventLoop::queue(function () {
                    $this->emit('listening');
                });
            } else {
                $this->emit('listening');
            }
        }

        public function closeAllConnections() {}

        public function close()
        {
            foreach (self::$registry as $port => $server) {
                if ($server === $this) {
                    unset(self::$registry[$port]);
                }
            }
            $this->emit('close');
        }
    }
}

if (!class_exists('PhpursHttpClientRequest')) {
    class PhpursHttpClientRequest extends PhpursHttpOutgoingMessage
    {
        public $path = '/';
        public $method = 'GET';
        public $host = 'localhost';
        public $protocol = 'http:';
        public $reusedSocket = false;
        public $options = null;
        public $started = false;
        public $bodyData = '';

        public function write($data)
        {
            $this->bodyData .= $data;
            if ($this->sendBody !== null) {
                ($this->sendBody)($data);
            }
            return true;
        }

        public function end($data = null)
        {
            if ($data !== null) {
                $this->write($data);
            }
            $this->start();
            return $this;
        }

        public function start()
        {
            if ($this->started) {
                return;
            }
            $this->started = true;
            PhpursHttpRuntime::perform($this);
        }
    }
}

if (!class_exists('PhpursHttpRuntime')) {
    class PhpursHttpRuntime
    {
        public static function parseUrl($url)
        {
            $parts = @parse_url($url);
            if ($parts === false) {
                return null;
            }
            return $parts;
        }

        private static function splitMessage($text)
        {
            $separator = strpos($text, "\r\n\r\n");
            $head = $separator === false ? $text : substr($text, 0, $separator);
            $body = $separator === false ? '' : substr($text, $separator + 4);
            $lines = explode("\r\n", $head);
            $firstLine = array_shift($lines);
            return [$firstLine, $lines, $body];
        }

        private static function parseHeaders($lines)
        {
            $headers = new \stdClass();
            $rawHeaders = [];
            foreach ($lines as $line) {
                $separator = strpos($line, ':');
                if ($separator === false) {
                    continue;
                }
                $name = substr($line, 0, $separator);
                $value = ltrim(substr($line, $separator + 1));
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
            return [$headers, $rawHeaders];
        }

        public static function parseRequest($text)
        {
            [$requestLine, $headerLines, $body] = self::splitMessage($text);
            [$headers, $rawHeaders] = self::parseHeaders($headerLines);
            $components = explode(' ', $requestLine);
            $method = $components[0] ?? 'GET';
            $url = $components[1] ?? '/';
            $httpVersion = isset($components[2]) ? str_replace('HTTP/', '', $components[2]) : '1.1';
            return [
                'method' => $method,
                'url' => $url,
                'httpVersion' => $httpVersion,
                'headers' => $headers,
                'rawHeaders' => $rawHeaders,
                'body' => $body,
            ];
        }

        public static function parseResponse($text)
        {
            [$statusLine, $headerLines, $body] = self::splitMessage($text);
            [$headers] = self::parseHeaders($headerLines);
            $status = 200;
            $statusMessage = 'OK';
            if (preg_match('/^HTTP\/[0-9.]+ ([0-9]+)(?: (.*))?$/', $statusLine, $matches)) {
                $status = (int) $matches[1];
                $statusMessage = $matches[2] ?? '';
            }
            return [
                'status' => $status,
                'statusMessage' => $statusMessage,
                'headers' => $headers,
                'body' => $body,
            ];
        }

        private static function serializeHeader($name, $value)
        {
            $text = '';
            foreach (is_array($value) ? $value : [$value] as $item) {
                $text .= "$name: $item\r\n";
            }
            return $text;
        }

        public static function makeRequestMessage($parsed, $socket)
        {
            $request = new PhpursHttpIncomingMessage();
            $request->url = $parsed['url'];
            $request->method = $parsed['method'];
            $request->httpVersion = $parsed['httpVersion'];
            $request->headers = $parsed['headers'];
            $request->rawHeaders = $parsed['rawHeaders'];
            $request->body = $parsed['body'];
            $request->socket = $socket;
            return $request;
        }

        public static function makeResponseMessage($parsed)
        {
            $response = new PhpursHttpIncomingMessage();
            $response->statusCode = $parsed['status'];
            $response->statusMessage = $parsed['statusMessage'];
            $response->headers = $parsed['headers'];
            $response->body = $parsed['body'];
            return $response;
        }

        public static function isUpgrade($headers)
        {
            $connection = strtolower($headers->connection ?? '');
            return isset($headers->upgrade) && strpos($connection, 'upgrade') !== false;
        }

        public static function buildResponseText($response)
        {
            $status = $response->statusCode;
            $message = $response->statusMessage !== '' ? $response->statusMessage : 'OK';
            $text = "HTTP/1.1 $status $message\r\n";
            $hasLength = false;
            foreach (get_object_vars($response->headers) as $key => $value) {
                if (strtolower($key) === 'content-length') {
                    $hasLength = true;
                }
                $text .= self::serializeHeader($key, $value);
            }
            if (!$hasLength) {
                $text .= 'Content-Length: ' . strlen($response->responseBody) . "\r\n";
            }
            $text .= "Connection: close\r\n\r\n";
            $text .= $response->responseBody;
            return $text;
        }

        public static function dispatch($server, $requestText)
        {
            $parsed = self::parseRequest($requestText);
            $socket = new PhpursHttpSocket();
            $request = self::makeRequestMessage($parsed, $socket);

            if (self::isUpgrade($parsed['headers'])) {
                $buffer = '';
                $socket->writeSink = function ($data) use (&$buffer) {
                    $buffer .= $data;
                };
                $server->emit('upgrade', $request, $socket, '');
                return $buffer;
            }

            $response = new PhpursHttpServerResponse();
            $response->req = $request;
            $response->responseBody = '';
            $response->sendBody = function ($data) use ($response) {
                $response->responseBody .= $data;
            };
            $server->emit('request', $request, $response);
            return self::buildResponseText($response);
        }

        public static function buildRequestText($request)
        {
            $options = $request->options ?? new \stdClass();
            $protocol = $options->protocol ?? $request->protocol;
            $host = $options->hostname ?? ($options->host ?? $request->host);
            $port = (int) ($options->port ?? ($protocol === 'https:' ? 443 : 80));
            $path = $options->path ?? $request->path;
            $method = $options->method ?? $request->method;
            $defaultPort = $protocol === 'https:' ? 443 : 80;
            $hostHeader = $port === $defaultPort ? $host : "$host:$port";
            $text = "$method $path HTTP/1.1\r\nHost: $hostHeader\r\n";
            if (!property_exists($request->headers, 'connection')) {
                $text .= "Connection: close\r\n";
            }
            foreach (get_object_vars($request->headers) as $key => $value) {
                if (strtolower($key) === 'host') {
                    continue;
                }
                $text .= self::serializeHeader($key, $value);
            }
            if ($request->bodyData !== '') {
                $text .= 'Content-Length: ' . strlen($request->bodyData) . "\r\n";
            }
            $text .= "\r\n" . $request->bodyData;
            return $text;
        }

        public static function perform($request)
        {
            $options = $request->options ?? new \stdClass();
            $protocol = $options->protocol ?? $request->protocol;
            $host = $options->hostname ?? ($options->host ?? $request->host);
            $port = (int) ($options->port ?? ($protocol === 'https:' ? 443 : 80));
            $text = self::buildRequestText($request);

            $local = null;
            if (
                ($host === 'localhost' || $host === '127.0.0.1') &&
                isset(PhpursHttpServer::$registry[$port])
            ) {
                $local = PhpursHttpServer::$registry[$port];
            }

            if ($local !== null) {
                $responseText = self::dispatch($local, $text);
            } else {
                $responseText = self::remoteRequest($text, $protocol, $host, $port, $options);
                if ($responseText === null) {
                    return;
                }
            }

            $parsedResponse = self::parseResponse($responseText);
            $response = self::makeResponseMessage($parsedResponse);
            if ($parsedResponse['status'] === 101) {
                $response->socket = new PhpursHttpSocket();
            }

            if (class_exists('\\Revolt\\EventLoop')) {
                \Revolt\EventLoop::queue(function () use ($request, $response) {
                    $request->emit('response', $response);
                });
            } else {
                $request->emit('response', $response);
            }
        }

        public static function remoteRequest($text, $protocol, $host, $port, $options)
        {
            $secure = $protocol === 'https:';
            $ssl = [];
            if (isset($options->rejectUnauthorized) && $options->rejectUnauthorized === false) {
                $ssl = ['verify_peer' => false, 'verify_peer_name' => false];
            }
            $context = stream_context_create($ssl === [] ? [] : ['ssl' => $ssl]);
            $remote = ($secure ? 'ssl://' : 'tcp://') . $host . ':' . $port;
            $client = @stream_socket_client(
                $remote,
                $errno,
                $errstr,
                15,
                STREAM_CLIENT_CONNECT,
                $context,
            );
            if ($client === false) {
                fwrite(STDERR, "HTTP request to $remote failed: $errstr\n");
                return null;
            }
            fwrite($client, $text);
            $responseText = stream_get_contents($client);
            fclose($client);
            return $responseText === false ? null : $responseText;
        }

        public static function requestFromUrl($url, $options = null)
        {
            $parts = self::parseUrl($url);
            $base = $options === null ? new \stdClass() : $options;
            if (!isset($base->protocol)) {
                $base->protocol = ($parts['scheme'] ?? 'http') . ':';
            }
            if (!isset($base->hostname) && !isset($base->host)) {
                $base->hostname = $parts['host'] ?? 'localhost';
            }
            if (!isset($base->port)) {
                $base->port = $parts['port'] ?? ($base->protocol === 'https:' ? 443 : 80);
            }
            if (!isset($base->path)) {
                $base->path =
                    ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
            }
            return self::makeRequest($base);
        }

        public static function makeRequest($options)
        {
            $request = new PhpursHttpClientRequest();
            $request->options = $options;
            $request->protocol = $options->protocol ?? 'http:';
            $request->host = $options->hostname ?? ($options->host ?? 'localhost');
            $request->path = $options->path ?? '/';
            $request->method = $options->method ?? 'GET';
            if (isset($options->headers)) {
                foreach ((array) $options->headers as $key => $value) {
                    $request->setHeader($key, $value);
                }
            }
            return $request;
        }
    }
}

$exports['maxHeaderSize'] = 16384;

$exports['createServer'] = function () {
    return new PhpursHttpServer(false);
};

$exports['createServerOptsImpl'] = function ($options) {
    return new PhpursHttpServer(false);
};

$exports['requestStrImpl'] = function ($url) {
    return PhpursHttpRuntime::requestFromUrl($url);
};

$exports['requestUrlImpl'] = function ($url) {
    return PhpursHttpRuntime::requestFromUrl($url);
};

$exports['requestStrOptsImpl'] = function ($url, $options) {
    return PhpursHttpRuntime::requestFromUrl($url, $options);
};

$exports['requestUrlOptsImpl'] = function ($url, $options) {
    return PhpursHttpRuntime::requestFromUrl($url, $options);
};

$exports['requestOptsImpl'] = function ($options) {
    return PhpursHttpRuntime::makeRequest($options);
};

$exports['getStrImpl'] = function ($url) {
    $request = PhpursHttpRuntime::requestFromUrl($url);
    $request->end();
    return $request;
};

$exports['getUrlImpl'] = function ($url) {
    $request = PhpursHttpRuntime::requestFromUrl($url);
    $request->end();
    return $request;
};

$exports['getStrOptsImpl'] = function ($url, $options) {
    $request = PhpursHttpRuntime::requestFromUrl($url, $options);
    $request->end();
    return $request;
};

$exports['getUrlOptsImpl'] = function ($url, $options) {
    $request = PhpursHttpRuntime::requestFromUrl($url, $options);
    $request->end();
    return $request;
};

$exports['getOptsImpl'] = function ($options) {
    $request = PhpursHttpRuntime::makeRequest($options);
    $request->end();
    return $request;
};

$exports['setMaxIdleHttpParsersImpl'] = function ($n) {};

return $exports;
