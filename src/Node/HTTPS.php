<?php

$exports = [];

// HTTPS shares the HTTP runtime; secure requests simply use the `https:`
// protocol, which selects a TLS client socket for remote hosts.
$exports['createSecureServer'] = function() { return new \Node\HTTP\PhpursHttpServer(true); };

$exports['createSecureServerOptsImpl'] = function($opts) { return new \Node\HTTP\PhpursHttpServer(true); };

$exports['requestStrImpl'] = function($url) {
    return ($GLOBALS['Node_HTTP_requestStrImpl'])($url);
};

$exports['requestUrlImpl'] = function($url) {
    return ($GLOBALS['Node_HTTP_requestUrlImpl'])($url);
};

$exports['requestStrOptsImpl'] = function($url, $opts) {
    return ($GLOBALS['Node_HTTP_requestStrOptsImpl'])($url, $opts);
};

$exports['requestUrlOptsImpl'] = function($url, $opts) {
    return ($GLOBALS['Node_HTTP_requestUrlOptsImpl'])($url, $opts);
};

$exports['requestOptsImpl'] = function($opts) {
    return ($GLOBALS['Node_HTTP_requestOptsImpl'])($opts);
};

$exports['getStrImpl'] = function($url) {
    return ($GLOBALS['Node_HTTP_getStrImpl'])($url);
};

$exports['getUrlImpl'] = function($url) {
    return ($GLOBALS['Node_HTTP_getUrlImpl'])($url);
};

$exports['getStrOptsImpl'] = function($url, $opts) {
    return ($GLOBALS['Node_HTTP_getStrOptsImpl'])($url, $opts);
};

$exports['getUrlOptsImpl'] = function($url, $opts) {
    return ($GLOBALS['Node_HTTP_getUrlOptsImpl'])($url, $opts);
};

$exports['getOptsImpl'] = function($opts) {
    return ($GLOBALS['Node_HTTP_getOptsImpl'])($opts);
};

return $exports;
