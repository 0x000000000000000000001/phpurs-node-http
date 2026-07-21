<?php

$exports = [];

$exports['setStatusCodeImpl'] = function($code, $res) {
    $res->setStatusCode($code);
};

$exports['req'] = function(...$args) { throw new \Exception("Function req is not implemented yet. PRs welcome!"); };
$exports['sendDateImpl'] = function(...$args) { throw new \Exception("Function sendDateImpl is not implemented yet. PRs welcome!"); };
$exports['setSendDateImpl'] = function(...$args) { throw new \Exception("Function setSendDateImpl is not implemented yet. PRs welcome!"); };
$exports['statusCodeImpl'] = function(...$args) { throw new \Exception("Function statusCodeImpl is not implemented yet. PRs welcome!"); };
$exports['statusMessageImpl'] = function(...$args) { throw new \Exception("Function statusMessageImpl is not implemented yet. PRs welcome!"); };
$exports['setStatusMessageImpl'] = function(...$args) { throw new \Exception("Function setStatusMessageImpl is not implemented yet. PRs welcome!"); };
$exports['strictContentLengthImpl'] = function(...$args) { throw new \Exception("Function strictContentLengthImpl is not implemented yet. PRs welcome!"); };
$exports['setStrictContentLengthImpl'] = function(...$args) { throw new \Exception("Function setStrictContentLengthImpl is not implemented yet. PRs welcome!"); };
$exports['writeEarlyHintsImpl'] = function(...$args) { throw new \Exception("Function writeEarlyHintsImpl is not implemented yet. PRs welcome!"); };
$exports['writeEarlyHintsCbImpl'] = function(...$args) { throw new \Exception("Function writeEarlyHintsCbImpl is not implemented yet. PRs welcome!"); };
$exports['writeHeadImpl'] = function(...$args) { throw new \Exception("Function writeHeadImpl is not implemented yet. PRs welcome!"); };
$exports['writeHeadMsgImpl'] = function(...$args) { throw new \Exception("Function writeHeadMsgImpl is not implemented yet. PRs welcome!"); };
$exports['writeHeadHeadersImpl'] = function(...$args) { throw new \Exception("Function writeHeadHeadersImpl is not implemented yet. PRs welcome!"); };
$exports['writeHeadMsgHeadersImpl'] = function(...$args) { throw new \Exception("Function writeHeadMsgHeadersImpl is not implemented yet. PRs welcome!"); };
$exports['writeProcessingImpl'] = function(...$args) { throw new \Exception("Function writeProcessingImpl is not implemented yet. PRs welcome!"); };

return $exports;
