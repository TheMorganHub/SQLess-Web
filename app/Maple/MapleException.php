<?php

namespace sqless\Maple;

use Exception;

class MapleException extends Exception {

    public function __construct(string $message = "") {
        parent::__construct($message, 0, null);
    }
}