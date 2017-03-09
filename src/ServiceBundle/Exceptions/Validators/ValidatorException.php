<?php

namespace ServiceBundle\Exceptions\Validators;

use Exception;

abstract class ValidatorException extends Exception implements ValidatorExceptionInterface
{
    protected $statusCode;

    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        if ($message !== "") {
            $this->message = $message;
        }
        parent::__construct($this->message, $code, $previous);
    }

    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
