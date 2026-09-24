<?php

$exports = [];

$exports['setHeaderImpl'] = function($key, $val, $res) {
    $res->setHeader($key, $val);
};

$exports['setHeaderArrImpl'] = function($key, $values, $res) {
    $res->setHeader($key, $values);
};

$exports['appendHeaderImpl'] = function($key, $value, $res) {
    $res->appendHeader($key, $value);
};

$exports['appendHeadersImpl'] = function($key, $values, $res) {
    $res->appendHeader($key, $values);
};

$exports['getHeaderImpl'] = function($key, $res) {
    $value = $res->getHeader($key);
    if ($value === null) { return null; }
    return is_array($value) ? implode(', ', $value) : $value;
};

$exports['getHeaderNamesImpl'] = function($name, $res) {
    return $res->getHeaderNames();
};

$exports['getHeadersImpl'] = function($res) {
    return $res->getHeaders();
};

$exports['hasHeaderImpl'] = function($key, $res) {
    return $res->hasHeader($key);
};

$exports['headersSentImpl'] = function($res) {
    return $res->headersSent ?? false;
};

$exports['removeHeaderImpl'] = function($key, $res) {
    $res->removeHeader($key);
};

$exports['addTrailersImpl'] = function($headers, $res) {
    $res->addTrailers((array) $headers);
};

$exports['flushHeadersImpl'] = function($res) {
    if (method_exists($res, 'flushHeaders')) { $res->flushHeaders(); }
};

$exports['setTimeoutImpl'] = function($ms, $res) {
};

$exports['socketImpl'] = function($res) {
    return $res->socket ?? null;
};

return $exports;
