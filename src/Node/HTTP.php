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

$exports['maxHeaderSize'] = function(...$args) { throw new \Exception("Function maxHeaderSize is not implemented yet. PRs welcome!"); };
$exports['requestStrImpl'] = function(...$args) { throw new \Exception("Function requestStrImpl is not implemented yet. PRs welcome!"); };
$exports['requestStrOptsImpl'] = function(...$args) { throw new \Exception("Function requestStrOptsImpl is not implemented yet. PRs welcome!"); };
$exports['requestUrlImpl'] = function(...$args) { throw new \Exception("Function requestUrlImpl is not implemented yet. PRs welcome!"); };
$exports['requestUrlOptsImpl'] = function(...$args) { throw new \Exception("Function requestUrlOptsImpl is not implemented yet. PRs welcome!"); };
$exports['requestOptsImpl'] = function(...$args) { throw new \Exception("Function requestOptsImpl is not implemented yet. PRs welcome!"); };
$exports['getStrImpl'] = function(...$args) { throw new \Exception("Function getStrImpl is not implemented yet. PRs welcome!"); };
$exports['getStrOptsImpl'] = function(...$args) { throw new \Exception("Function getStrOptsImpl is not implemented yet. PRs welcome!"); };
$exports['getUrlImpl'] = function(...$args) { throw new \Exception("Function getUrlImpl is not implemented yet. PRs welcome!"); };
$exports['getUrlOptsImpl'] = function(...$args) { throw new \Exception("Function getUrlOptsImpl is not implemented yet. PRs welcome!"); };
$exports['getOptsImpl'] = function(...$args) { throw new \Exception("Function getOptsImpl is not implemented yet. PRs welcome!"); };
$exports['setMaxIdleHttpParsersImpl'] = function(...$args) { throw new \Exception("Function setMaxIdleHttpParsersImpl is not implemented yet. PRs welcome!"); };

return $exports;
