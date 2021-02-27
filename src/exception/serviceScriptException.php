<?php

    namespace tomdom\core\exception;

    use \Exception;

    class serviceScriptException extends Exception{
        function __construct($message = '', $code = 0) {
            parent::__construct($message, $code);
        }
    }