<?php

namespace DomainBundle\Entity\Orders;

/**
 * Order
 */
class Order
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdDatetime;

    /**
     * @var \DateTime
     */
    private $updatedDatetime;

    /**
     * @var string
     */
    private $uniqueNumber;

    /**
     * @var \DateTime
     */
    private $startDatetime;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $organization;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statuses;

    /**
     * @var \ApiBundle\Entity\User
     */
    private $createdBy;

    /**
     * @var \ApiBundle\Entity\User
     */
    private $updatedBy;

    /**
     * @var \ApiBundle\Entity\User
     */
    private $customer;

    /**
     * @var \ApiBundle\Entity\User
     */
    private $manager;

    /**
     * @var \ApiBundle\Entity\User
     */
    private $worker;
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $estimates;
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $checklist;
    /**
     * @var \DomainBundle\Entity\Orders\OrderFeedback
     */
    private $feedback;
    /**
     * @var \DomainBundle\Entity\Customer\DictCustomerAddress
     */
    private $address;

    /**
     * Order constructor.
     */
    public function __construct()
    {
        $this->statuses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->estimates = new \Doctrine\Common\Collections\ArrayCollection();
        $this->checklist = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get createdDatetime
     *
     * @return \DateTime
     */
    public function getCreatedDatetime()
    {
        return $this->createdDatetime;
    }

    /**
     * Set createdDatetime
     *
     * @param \DateTime $createdDatetime
     *
     * @return Order
     */
    public function setCreatedDatetime($createdDatetime)
    {
        $this->createdDatetime = $createdDatetime;

        return $this;
    }

    /**
     * Get updatedDatetime
     *
     * @return \DateTime
     */
    public function getUpdatedDatetime()
    {
        return $this->updatedDatetime;
    }

    /**
     * Set updatedDatetime
     *
     * @param \DateTime $updatedDatetime
     *
     * @return Order
     */
    public function setUpdatedDatetime($updatedDatetime)
    {
        $this->updatedDatetime = $updatedDatetime;

        return $this;
    }

    /**
     * Get uniqueNumber
     *
     * @return string
     */
    public function getUniqueNumber()
    {
        return $this->uniqueNumber;
    }

    /**
     * Set uniqueNumber
     *
     * @param string $uniqueNumber
     *
     * @return Order
     */
    public function setUniqueNumber($uniqueNumber)
    {
        $this->uniqueNumber = $uniqueNumber;

        return $this;
    }

    /**
     * Get startDatetime
     *
     * @return \DateTime
     */
    public function getStartDatetime()
    {
        return $this->startDatetime;
    }

    /**
     * Set startDatetime
     *
     * @param \DateTime $startDatetime
     *
     * @return Order
     */
    public function setStartDatetime($startDatetime)
    {
        $this->startDatetime = $startDatetime;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Order
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set organization
     *
     * @param string $organization
     *
     * @return Order
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Add status
     *
     * @param \DomainBundle\Entity\Orders\OrderStatus $status
     *
     * @return Order
     */
    public function addStatus(\DomainBundle\Entity\Orders\OrderStatus $status)
    {
        $this->statuses[] = $status;

        return $this;
    }

    /**
     * Remove status
     *
     * @param \DomainBundle\Entity\Orders\OrderStatus $status
     */
    public function removeStatus(\DomainBundle\Entity\Orders\OrderStatus $status)
    {
        $this->statuses->removeElement($status);
    }

    /**
     * Get statuses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Get createdBy
     *
     * @return \ApiBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdBy
     *
     * @param \ApiBundle\Entity\User $createdBy
     *
     * @return Order
     */
    public function setCreatedBy(\ApiBundle\Entity\User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ApiBundle\Entity\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ApiBundle\Entity\User $updatedBy
     *
     * @return Order
     */
    public function setUpdatedBy(\ApiBundle\Entity\User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \ApiBundle\Entity\User
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set customer
     *
     * @param \ApiBundle\Entity\User $customer
     *
     * @return Order
     */
    public function setCustomer(\ApiBundle\Entity\User $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get manager
     *
     * @return \ApiBundle\Entity\User
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set manager
     *
     * @param \ApiBundle\Entity\User $manager
     *
     * @return Order
     */
    public function setManager(\ApiBundle\Entity\User $manager = null)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get worker
     *
     * @return \ApiBundle\Entity\User
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * Set worker
     *
     * @param \ApiBundle\Entity\User $worker
     *
     * @return Order
     */
    public function setWorker(\ApiBundle\Entity\User $worker = null)
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * Add estimate
     *
     * @param \DomainBundle\Entity\Orders\OrderEstimate $estimate
     *
     * @return Order
     */
    public function addEstimate(\DomainBundle\Entity\Orders\OrderEstimate $estimate)
    {
        $this->estimates[] = $estimate;

        return $this;
    }

    /**
     * Remove estimate
     *
     * @param \DomainBundle\Entity\Orders\OrderEstimate $estimate
     */
    public function removeEstimate(\DomainBundle\Entity\Orders\OrderEstimate $estimate)
    {
        $this->estimates->removeElement($estimate);
    }

    /**
     * Get estimates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEstimates()
    {
        return $this->estimates;
    }

    /**
     * Add checklist
     *
     * @param \DomainBundle\Entity\Orders\OrderChecklistPoint $checklist
     *
     * @return Order
     */
    public function addChecklist(\DomainBundle\Entity\Orders\OrderChecklistPoint $checklist)
    {
        $this->checklist[] = $checklist;

        return $this;
    }

    /**
     * Remove checklist
     *
     * @param \DomainBundle\Entity\Orders\OrderChecklistPoint $checklist
     */
    public function removeChecklist(\DomainBundle\Entity\Orders\OrderChecklistPoint $checklist)
    {
        $this->checklist->removeElement($checklist);
    }

    /**
     * Get checklist
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChecklist()
    {
        return $this->checklist;
    }

    /**
     * Get feedback
     *
     * @return \DomainBundle\Entity\Orders\OrderFeedback
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Set feedback
     *
     * @param \DomainBundle\Entity\Orders\OrderFeedback $feedback
     *
     * @return Order
     */
    public function setFeedback(\DomainBundle\Entity\Orders\OrderFeedback $feedback = null)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * Get address
     *
     * @return \DomainBundle\Entity\Customer\DictCustomerAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param \DomainBundle\Entity\Customer\DictCustomerAddress $address
     *
     * @return Order
     */
    public function setAddress(\DomainBundle\Entity\Customer\DictCustomerAddress $address = null)
    {
        $this->address = $address;

        return $this;
    }
}
