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

$exports['completeImpl'] = function(...$args) { throw new \Exception("Function completeImpl is not implemented yet. PRs welcome!"); };
$exports['headersDistinct'] = function(...$args) { throw new \Exception("Function headersDistinct is not implemented yet. PRs welcome!"); };
$exports['httpVersion'] = function(...$args) { throw new \Exception("Function httpVersion is not implemented yet. PRs welcome!"); };
$exports['rawHeaders'] = function(...$args) { throw new \Exception("Function rawHeaders is not implemented yet. PRs welcome!"); };
$exports['rawTrailersImpl'] = function(...$args) { throw new \Exception("Function rawTrailersImpl is not implemented yet. PRs welcome!"); };
$exports['socketImpl'] = function(...$args) { throw new \Exception("Function socketImpl is not implemented yet. PRs welcome!"); };
$exports['statusCode'] = function(...$args) { throw new \Exception("Function statusCode is not implemented yet. PRs welcome!"); };
$exports['statusMessage'] = function(...$args) { throw new \Exception("Function statusMessage is not implemented yet. PRs welcome!"); };
$exports['trailersImpl'] = function(...$args) { throw new \Exception("Function trailersImpl is not implemented yet. PRs welcome!"); };
$exports['trailersDistinctImpl'] = function(...$args) { throw new \Exception("Function trailersDistinctImpl is not implemented yet. PRs welcome!"); };

return $exports;
