<?php
namespace ApiBundle\Transformers;

use ApiBundle\Service\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use DomainBundle\Entity\Orders\Order;
use DomainBundle\Entity\Orders\OrderEstimate;
use League\Fractal;
use Symfony\Component\Translation\TranslatorInterface;

class OrderReadTransformer extends Fractal\TransformerAbstract
{
    /**
     * @var UserManager
     */
    public $userManager;

    /**
     * @var EntityManager
     */
    public $em;

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


    public function __construct(EntityManager $em, $userManager, $translator = null)
    {
        $this->translator = $translator;
        $this->em = $em;
        $this->userManager = $userManager;
    }

    public function transform(Order $order)
    {
        $data = [
            'id'                => (int)$order->getId(),
            'created_timestamp' => !empty($order->getCreatedDatetime()) ? $order->getCreatedDatetime()->getTimestamp() : null
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

    /**
     * include customer data
     *
     * @param Order $order
     * @return \League\Fractal\Resource\Item
     */
    public function includeCustomer(Order $order)
    {
        return $this->item($order->getCustomer(), new UserReadTransformer());
    }


    /**
     * include customer data
     *
     * @param Order $order
     * @return \League\Fractal\Resource\Item
     */
    public function includeManager(Order $order)
    {
        $data = $order->getManager() ? $order->getManager() : null;
        return $this->item($data, new UserReadTransformer());
    }


    /**
     * include customer data
     *
     * @param Order $order
     * @return \League\Fractal\Resource\Item
     */
    public function includeWorker(Order $order)
    {
        $data = $order->getWorker() ? $order->getWorker() : null;
        return $this->item($data, new WorkerReadTransformer($this->userManager));
    }


    /**
     * Include order current status
     *
     * @param Order $order
     * @return Fractal\Resource\Item|Fractal\Resource\NullResource
     */
    public function includeStatus(Order $order)
    {
        if (!empty($order->getStatuses())) {
            $iterator = $order->getStatuses()->getIterator();
            $iterator->uasort(function ($a, $b) {
                return ($a->getCreatedDatetime() > $b->getCreatedDatetime()) ? -1 : 1;
            });
            $collection = new ArrayCollection(iterator_to_array($iterator));
            return $this->item($collection->first(), new OrderStatusReadTransformer($this->translator));
        } else {
            return $this->null();
        }
    }


    /**
     * Include order current estimation
     *
     * @param Order $order
     * @return Fractal\Resource\Item|Fractal\Resource\NullResource
     */
    public function includeEstimate(Order $order)
    {
        $data = ($order->getEstimates() instanceof Collection) && ($order->getEstimates()->first() instanceof OrderEstimate) ? $order->getEstimates()->first() : null;
        return $this->item($data, new OrderEstimateReadTransformer());
    }


    /**
     * Include order current estimation
     *
     * @param Order $order
     * @return Fractal\Resource\Item|Fractal\Resource\NullResource
     */
    public function includeAddress(Order $order)
    {
        return $this->item($order->getAddress(), new CustomerAddressReadTransformer());
    }


    /**
     * Include order current estimation
     *
     * @param Order $order
     * @return Fractal\Resource\Item|Fractal\Resource\NullResource
     */
    public function includeChecklist(Order $order)
    {
        $data = ($order->getChecklist() instanceof Collection) ? $order->getChecklist() : [];
        return $this->collection($data, new OrderChecklistPointReadTransformer($this->em->getRepository("DomainBundle:Orders\\OrderChecklistPoint")));
    }


    /**
     * Include order current estimation
     *
     * @param Order $order
     * @return Fractal\Resource\Item|Fractal\Resource\NullResource
     */
    public function includeFeedback(Order $order)
    {
        $data = $order->getFeedback() ? $order->getFeedback() : null;
        return $this->item($data, new OrderFeedbackReadTransformer());
    }
}