<?php


namespace ServiceBundle\Exceptions\Handlers;

interface HandlerExceptionInterface
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
