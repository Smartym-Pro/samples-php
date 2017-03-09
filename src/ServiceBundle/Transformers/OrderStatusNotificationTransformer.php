<?php

namespace ServiceBundle\Transformers;

use ApiBundle\Service\UserManager;
use ApiBundle\Transformers\CustomerAddressReadTransformer;
use ApiBundle\Transformers\OrderChecklistPointReadTransformer;
use ApiBundle\Transformers\OrderEstimateReadTransformer;
use ApiBundle\Transformers\OrderFeedbackReadTransformer;
use ApiBundle\Transformers\OrderStatusReadTransformer;
use ApiBundle\Transformers\UserReadTransformer;
use ApiBundle\Transformers\WorkerReadTransformer;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use DomainBundle\Entity\Orders\Order;
use DomainBundle\Entity\Orders\OrderEstimate;
use League\Fractal\TransformerAbstract;
use DomainBundle\Entity\Orders\OrderStatus;
use Symfony\Component\Translation\TranslatorInterface;

class OrderStatusNotificationTransformer extends TransformerAbstract
{
    /**
     * @var UserManager
     */
    public $userManager;

    /**
     * @var EntityRepository
     */
    public $orderCheckListPointRepository;

    /**
     * @var TranslatorInterface
     */
    public $translator;

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'customer',
        'worker',
        'manager',
        'status',
        'estimate',
        'address',
        'checklist',
        'feedback',
    ];

    public function __construct(EntityRepository $orderCheckListPointRepository, $userManager, $translator = null)
    {
        $this->translator = $translator;
        $this->orderCheckListPointRepository = $orderCheckListPointRepository;
        $this->userManager = $userManager;
    }

    public function transform(OrderStatus $orderStatus)
    {
        $this->setMessage($orderStatus);


        /** @var Order $order */
        $order = $orderStatus->getOrder();

        $data = [
            'id'                => (int) $order->getId(),
            'created_timestamp' => $order->getCreatedDatetime() !== null ? $order->getCreatedDatetime()->getTimestamp() : null,
        ];

        if ($order->getStartDatetime() instanceof \DateTime) {
            $data['start_timestamp'] = $order->getStartDatetime()->getTimestamp();
        }

        if ($order->getUniqueNumber()) {
            $data['unique_number'] = $order->getUniqueNumber();
        }

        if ($order->getDescription()) {
            $data['description'] = $order->getDescription();
        }

        if ($order->getOrganization()) {
            $data['organization'] = $order->getOrganization();
        }

        return $data;
    }

    protected function setMessage(OrderStatus $orderStatus)
    {
        $this->getCurrentScope()->getResource()->setMeta(
            [
                'message' => $this->translator->trans(
                    'Status of order order_number is "status"', [
                    'order_number' => $orderStatus->getOrder()->getUniqueNumber(),
                    'status'       => $this->translator->trans(
                        'order.status.' . $orderStatus->getStatus(),
                        [],
                        'orders'
                    ),
                ], 'orders'),
            ]
        );
    }

    /**
     * include customer data
     *
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Item
     */
    public function includeCustomer(OrderStatus $orderStatus)
    {
        /** @var Order $order */
        $order = $orderStatus->getOrder();
        return $this->item($order->getCustomer(), new UserReadTransformer());
    }


    /**
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Item
     */
    public function includeManager(OrderStatus $orderStatus)
    {
        /** @var Order $order */
        $order = $orderStatus->getOrder();
        $data = $order->getManager() ? $order->getManager() : null;
        return $this->item($data, new UserReadTransformer());
    }


    /**
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Item
     */
    public function includeWorker(OrderStatus $orderStatus)
    {
        /** @var Order $order */
        $order = $orderStatus->getOrder();
        $data = $order->getWorker() ? $order->getWorker() : null;
        return $this->item($data, new WorkerReadTransformer($this->userManager));
    }


    /**
     * Include order current status
     *
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeStatus(OrderStatus $orderStatus)
    {
        return $this->item($orderStatus, new OrderStatusReadTransformer($this->translator));
    }


    /**
     * Include order current estimation
     *
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Item
     */
    public function includeEstimate(OrderStatus $orderStatus)
    {
        /** @var Order $order */
        $order = $orderStatus->getOrder();
        $data = ($order->getEstimates() instanceof Collection) && ($order->getEstimates()->first() instanceof OrderEstimate) ? $order->getEstimates()->first() : null;
        return $this->item($data, new OrderEstimateReadTransformer());
    }


    /**
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Item
     */
    public function includeAddress(OrderStatus $orderStatus)
    {
        /** @var Order $order */
        $order = $orderStatus->getOrder();
        return $this->item($order->getAddress(), new CustomerAddressReadTransformer());
    }


    /**
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChecklist(OrderStatus $orderStatus)
    {
        /** @var Order $order */
        $order = $orderStatus->getOrder();
        $data = ($order->getChecklist() instanceof Collection) ? $order->getChecklist() : [];
        return $this->collection($data,
            new OrderChecklistPointReadTransformer($this->orderCheckListPointRepository));
    }


    /**
     * @param OrderStatus $orderStatus
     * @return \League\Fractal\Resource\Item
     */
    public function includeFeedback(OrderStatus $orderStatus)
    {
        /** @var Order $order */
        $order = $orderStatus->getOrder();
        return $this->item($order->getFeedback(), new OrderFeedbackReadTransformer());
    }
}
