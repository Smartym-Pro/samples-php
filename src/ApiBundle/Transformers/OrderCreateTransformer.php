<?php
namespace ApiBundle\Transformers;

use DomainBundle\Entity\Orders\Order;
use League\Fractal;

class OrderCreateTransformer extends Fractal\TransformerAbstract
{
    public function transform(Order $order)
    {
        return [
            'id' => $order->getId()
        ];
    }
}