<?php

$exports = [];

$exports['setStatusCodeImpl'] = function($code, $res) {
    $res->setStatusCode($code);
};

return $exports;
