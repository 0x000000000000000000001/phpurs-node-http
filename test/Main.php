<?php

$exports = [];

$exports['setTimeoutImpl'] = function($ms, $effect) {
    if (class_exists('\\Revolt\\EventLoop')) {
        \Revolt\EventLoop::delay($ms / 1000, function() use ($effect) {
            $effect($GLOBALS['Data_Unit_unit']);
        });
    } else {
        $effect($GLOBALS['Data_Unit_unit']);
    }
};

$exports['stdout'] = new class {
    public function write($data) { echo $data; return true; }
    public function end($data = null) { if ($data !== null) { echo $data; } return $this; }
    public function on($event, $cb) { return $this; }
    public function emit($event, ...$args) { return true; }
};

return $exports;
