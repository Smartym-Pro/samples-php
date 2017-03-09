<?php

namespace ServiceBundle\Commands;

abstract class BaseCommandAbstract
{
    /**
     * AutoFillDataCommandTrait constructor.
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }
}