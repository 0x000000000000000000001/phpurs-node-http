<?php
// TODO: This FFI module is currently a stub to allow compilation.
// If you need actual implementations, PRs are very welcome!

$exports['path'] = function($cr) { return $cr->path ?? ''; };
$exports['method'] = function($cr) { return $cr->method ?? ''; };
$exports['host'] = function($cr) { return $cr->host ?? ''; };
$exports['protocol'] = function($cr) { return $cr->protocol ?? ''; };
$exports['reusedSocket'] = function($cr) { return $cr->reusedSocket ?? false; };
$exports['setNoDelayImpl'] = function($d, $cr) { return $cr; };
$exports['setSocketKeepAliveImpl'] = function($b, $ms, $cr) { return $cr; };
$exports['setTimeoutImpl'] = function($ms, $cr) { return $cr; };
return $exports;
