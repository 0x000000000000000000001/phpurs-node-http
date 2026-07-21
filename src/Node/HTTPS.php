<?php
// TODO: This FFI module is currently a stub to allow compilation.
// If you need actual implementations, PRs are very welcome!

$exports['createSecureServer'] = function() { return new \stdClass(); };
$exports['createSecureServerOptsImpl'] = function($opts) { return new \stdClass(); };
$exports['requestStrImpl'] = function($url) { return new \stdClass(); };
$exports['requestStrOptsImpl'] = function($url, $opts) { return new \stdClass(); };
$exports['requestUrlImpl'] = function($url) { return new \stdClass(); };
$exports['requestUrlOptsImpl'] = function($url, $opts) { return new \stdClass(); };
$exports['requestOptsImpl'] = function($opts) { return new \stdClass(); };
$exports['getStrImpl'] = function($url) { return new \stdClass(); };
$exports['getStrOptsImpl'] = function($url, $opts) { return new \stdClass(); };
$exports['getUrlImpl'] = function($url) { return new \stdClass(); };
$exports['getUrlOptsImpl'] = function($url, $opts) { return new \stdClass(); };
$exports['getOptsImpl'] = function($opts) { return new \stdClass(); };
return $exports;
