<?php

$exports = [];

$exports['url'] = function($req) {
    return $req->url;
};

$exports['method'] = function($req) {
    return $req->method;
};

$exports['headersImpl'] = function($req) {
    return $req->headers;
};

$exports['completeImpl'] = function($req) {
    return $req->complete ?? true;
};

$exports['headersDistinct'] = function($req) {
    $out = new \stdClass();
    foreach (($req->headers ?? []) as $key => $value) {
        $out->{$key} = is_array($value) ? $value : [$value];
    }
    return $out;
};

$exports['httpVersion'] = function($req) {
    return $req->httpVersion ?? '1.1';
};

$exports['rawHeaders'] = function($req) {
    return $req->rawHeaders ?? [];
};

$exports['rawTrailersImpl'] = function($req) {
    return null;
};

$exports['socketImpl'] = function($req) {
    return $req->socket ?? null;
};

$exports['statusCode'] = function($req) {
    return $req->statusCode ?? 0;
};

$exports['statusMessage'] = function($req) {
    return $req->statusMessage ?? '';
};

$exports['trailersImpl'] = function($req) {
    return null;
};

$exports['trailersDistinctImpl'] = function($req) {
    return null;
};

return $exports;
