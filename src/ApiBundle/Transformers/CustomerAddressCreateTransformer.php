<?php
namespace ApiBundle\Transformers;

use DomainBundle\Entity\Customer\DictCustomerAddress;
use League\Fractal;


class CustomerAddressCreateTransformer extends Fractal\TransformerAbstract
{
    public function transform(DictCustomerAddress $entity)
    {
        return [
            'id'   => $entity->getId()
        ];
    }
}