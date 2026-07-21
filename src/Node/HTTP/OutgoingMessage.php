<?php

$exports = [];

$exports['setHeaderImpl'] = function($key, $val, $res) {
    $res->setHeader($key, $val);
};

$exports['addTrailersImpl'] = function(...$args) { throw new \Exception("Function addTrailersImpl is not implemented yet. PRs welcome!"); };
$exports['appendHeaderImpl'] = function(...$args) { throw new \Exception("Function appendHeaderImpl is not implemented yet. PRs welcome!"); };
$exports['appendHeadersImpl'] = function(...$args) { throw new \Exception("Function appendHeadersImpl is not implemented yet. PRs welcome!"); };
$exports['flushHeadersImpl'] = function(...$args) { throw new \Exception("Function flushHeadersImpl is not implemented yet. PRs welcome!"); };
$exports['getHeaderImpl'] = function(...$args) { throw new \Exception("Function getHeaderImpl is not implemented yet. PRs welcome!"); };
$exports['getHeaderNamesImpl'] = function(...$args) { throw new \Exception("Function getHeaderNamesImpl is not implemented yet. PRs welcome!"); };
$exports['getHeadersImpl'] = function(...$args) { throw new \Exception("Function getHeadersImpl is not implemented yet. PRs welcome!"); };
$exports['hasHeaderImpl'] = function(...$args) { throw new \Exception("Function hasHeaderImpl is not implemented yet. PRs welcome!"); };
$exports['headersSentImpl'] = function(...$args) { throw new \Exception("Function headersSentImpl is not implemented yet. PRs welcome!"); };
$exports['removeHeaderImpl'] = function(...$args) { throw new \Exception("Function removeHeaderImpl is not implemented yet. PRs welcome!"); };
$exports['setHeaderArrImpl'] = function(...$args) { throw new \Exception("Function setHeaderArrImpl is not implemented yet. PRs welcome!"); };
$exports['setTimeoutImpl'] = function(...$args) { throw new \Exception("Function setTimeoutImpl is not implemented yet. PRs welcome!"); };
$exports['socketImpl'] = function(...$args) { throw new \Exception("Function socketImpl is not implemented yet. PRs welcome!"); };

return $exports;
