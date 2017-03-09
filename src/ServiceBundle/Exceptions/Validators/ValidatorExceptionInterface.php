<?php

namespace ServiceBundle\Exceptions\Validators;

interface ValidatorExceptionInterface
{
    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode();

    /**
     * Returns the error code.
     *
     * @return int
     */
    public function getCode();

    /**
     * Returns the error text.
     *
     * @return string
     */
    public function getMessage();
}
