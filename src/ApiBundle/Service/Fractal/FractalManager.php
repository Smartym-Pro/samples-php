<?php

namespace ApiBundle\Service\Fractal;

use League\Fractal\Manager;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class FractalManager
{
    /**
     * @var Manager
     */
    public $fractal;

    /**
     * FractalManager constructor.
     * @param SerializerAbstract $serializer
     */
    public function __construct(SerializerAbstract $serializer)
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer($serializer);
    }

    /**
     * @param object[]|object $data
     * @param TransformerAbstract $transformer
     * @param PaginatorInterface|null $paginator
     * @return \League\Fractal\Scope
     */
    public function createData($data, TransformerAbstract $transformer, PaginatorInterface $paginator = null)
    {
        $resource = $this->createResource($data, $transformer, $paginator);
        return $this->createDataFromResource($resource);
    }

    /**
     * @param ResourceInterface $resource
     * @return Scope
     */
    public function createDataFromResource(ResourceInterface $resource)
    {
        return $this->fractal->createData($resource);
    }

    /**
     * @param $data
     * @param TransformerAbstract $transformer
     * @param PaginatorInterface|null $paginator
     * @return Collection|Item
     */
    public function createResource($data, TransformerAbstract $transformer, PaginatorInterface $paginator = null)
    {
        if (is_array($data)) {
            $resource = new Collection($data, $transformer);
            if ($paginator) {
                $resource->setPaginator($paginator);
            }
        } else {
            $resource = new Item($data, $transformer);
        }

        return $resource;
    }

    /**
     * @param object[]|object $data
     * @param TransformerAbstract $transformer
     * @param PaginatorInterface|null $paginator
     * @return string
     */
    public function toJson($data, TransformerAbstract $transformer, PaginatorInterface $paginator = null)
    {
        return $this->createData($data, $transformer, $paginator)->toJson();
    }

    /**
     * @param object[]|object $data
     * @param TransformerAbstract $transformer
     * @param PaginatorInterface|null $paginator
     * @return array
     */
    public function toArray($data, TransformerAbstract $transformer, PaginatorInterface $paginator = null)
    {
        return $this->createData($data, $transformer, $paginator)->toArray();
    }
}