<?php

$exports = [];

$exports['bytesParsed'] = function($error) {
    return 0;
};

$exports['rawPacket'] = function($error) {
    return '';
};

$exports['closeAllConnectionsImpl'] = function($server) {
    if (method_exists($server, 'closeAllConnections')) { $server->closeAllConnections(); }
};

$exports['closeIdleConnectionsImpl'] = function($server) {
    if (method_exists($server, 'closeAllConnections')) { $server->closeAllConnections(); }
};

$exports['headersTimeoutImpl'] = function($server) {
    return $server->headersTimeout ?? 0;
};

$exports['setHeadersTimeoutImpl'] = function($value, $server) {
    $server->headersTimeout = $value;
};

$exports['maxHeadersCountImpl'] = function($server) {
    return $server->maxHeadersCount ?? 0;
};

$exports['setMaxHeadersCountImpl'] = function($value, $server) {
    $server->maxHeadersCount = $value;
};

$exports['requestTimeoutImpl'] = function($server) {
    return $server->requestTimeout ?? 0;
};

$exports['setRequestTimeoutImpl'] = function($value, $server) {
    $server->requestTimeout = $value;
};

$exports['maxRequestsPerSocketImpl'] = function($server) {
    return $server->maxRequestsPerSocket ?? 0;
};

$exports['setMaxRequestsPerSocketImpl'] = function($value, $server) {
    $server->maxRequestsPerSocket = $value;
};

$exports['timeoutImpl'] = function($server) {
    return $server->timeout ?? 0;
};

$exports['setTimeoutImpl'] = function($value, $server) {
    $server->timeout = $value;
};

$exports['keepAliveTimeoutImpl'] = function($server) {
    return $server->keepAliveTimeout ?? 0;
};

$exports['setKeepAliveTimeoutImpl'] = function($value, $server) {
    $server->keepAliveTimeout = $value;
};

return $exports;
