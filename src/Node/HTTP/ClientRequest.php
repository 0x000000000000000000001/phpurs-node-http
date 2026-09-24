<?php

$exports = [];

$exports['path'] = function($cr) { return $cr->path ?? ''; };
$exports['method'] = function($cr) { return $cr->method ?? ''; };
$exports['host'] = function($cr) { return $cr->host ?? ''; };
$exports['protocol'] = function($cr) { return $cr->protocol ?? ''; };
$exports['reusedSocket'] = function($cr) { return $cr->reusedSocket ?? false; };
$exports['setNoDelayImpl'] = function($value, $cr) {};
$exports['setSocketKeepAliveImpl'] = function($enable, $ms, $cr) {};
$exports['setTimeoutImpl'] = function($ms, $cr) {};

return $exports;
