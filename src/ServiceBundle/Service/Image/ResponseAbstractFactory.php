<?php

namespace ServiceBundle\Service\Image;

use Symfony\Component\HttpFoundation\RequestStack;
use League\Glide\Responses\SymfonyResponseFactory;

class ResponseAbstractFactory
{
    /**
     * @param RequestStack $requestStack
     * @return SymfonyResponseFactory
     */
    public static function createFactory(RequestStack $requestStack)
    {
        return new SymfonyResponseFactory($requestStack->getCurrentRequest());
    }
}
