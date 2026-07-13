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

return $exports;
