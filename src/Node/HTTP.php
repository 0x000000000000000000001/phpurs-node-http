<?php

$exports = [];

$createServerImpl = function() {
    $server = new \stdClass();
    $server->requestListener = null;

    $server->on = function($event, $listener) use ($server) {
        if ($event === 'request') {
            $server->requestListener = $listener;
        }
    };

    return $server;
};

$exports['createServer'] = $createServerImpl;

$exports['createServerOptsImpl'] = function($opts) use ($createServerImpl) {
    return $createServerImpl();
};

return $exports;
