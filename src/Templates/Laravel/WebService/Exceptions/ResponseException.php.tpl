<?php

namespace App\Exceptions\Dummy;

use Exception;

class ResponseException extends Exception
{
    public function __construct($message = "", $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json(["message" => "Server not responding."],500);
    }
}
