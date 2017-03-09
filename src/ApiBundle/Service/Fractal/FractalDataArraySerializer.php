<?php

namespace ApiBundle\Service\Fractal;

use League\Fractal\Serializer\ArraySerializer;

class FractalDataArraySerializer extends ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return ['data' => $data];
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        if (empty($data)) {
            $data = (object)[];
        }
        return ['data' => $data];
    }

    /**
     * Serialize null resource.
     *
     * @return array
     */
    public function null()
    {
        return ['data' => []];
    }

    /**
     * Serialize null resource for object.
     *
     * @return array
     */
    public function nullDataForObject()
    {
        return ['data' => (object)[]];
    }
}
