<?php

$exports = [];

$exports['setHeaderImpl'] = function($key, $val, $res) {
    $res->setHeader($key, $val);
};

return $exports;
