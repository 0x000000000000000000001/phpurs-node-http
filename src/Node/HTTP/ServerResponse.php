<?php

$exports = [];

$exports['setStatusCodeImpl'] = function($code, $res) {
    $res->setStatusCode($code);
};

$exports['req'] = function($res) {
    return $res->req;
};

$exports['sendDateImpl'] = function($res) {
    return $res->sendDate ?? true;
};

$exports['setSendDateImpl'] = function($value, $res) {
    $res->sendDate = $value;
};

$exports['statusCodeImpl'] = function($res) {
    return $res->statusCode ?? 200;
};

$exports['statusMessageImpl'] = function($res) {
    return $res->statusMessage ?? '';
};

$exports['setStatusMessageImpl'] = function($message, $res) {
    $res->statusMessage = $message;
};

$exports['strictContentLengthImpl'] = function($res) {
    return $res->strictContentLength ?? false;
};

$exports['setStrictContentLengthImpl'] = function($value, $res) {
    $res->strictContentLength = $value;
};

$exports['writeEarlyHintsImpl'] = function($hints, $res) {
};

$exports['writeEarlyHintsCbImpl'] = function($hints, $cb, $res) {
    $cb();
};

$exports['writeHeadImpl'] = function($code, $res) {
    $res->writeHead($code);
};

$exports['writeHeadMsgImpl'] = function($code, $message, $res) {
    $res->statusMessage = $message;
    $res->writeHead($code);
};

$exports['writeHeadHeadersImpl'] = function($code, $headers, $res) {
    $res->writeHead($code, (array) $headers);
};

$exports['writeHeadMsgHeadersImpl'] = function($code, $message, $headers, $res) {
    $res->statusMessage = $message;
    $res->writeHead($code, (array) $headers);
};

$exports['writeProcessingImpl'] = function($res) {
};

return $exports;
