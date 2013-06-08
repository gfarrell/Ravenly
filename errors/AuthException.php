<?php

namespace Ravenly\Errors;

use Log;
use Exception;

class AuthException extends Exception {
    public function __construct($message) {
        Log::error('Ravenly - authentication error: '.$message);
        $this->message = $message;
    }
}
?>