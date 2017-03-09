<?php

namespace DomainBundle\Entity\Customer;

/**
 * DictCustomerAddress
 */
class DictCustomerAddress
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
    private $title;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $x;

    /**
     * @var string
     */
    private $y;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdDatetime
     *
     * @param \DateTime $createdDatetime
     *
     * @return DictCustomerAddress
     */
    public function setCreatedDatetime($createdDatetime)
    {
        $this->createdDatetime = $createdDatetime;

        return $this;
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
     * Set updatedDatetime
     *
     * @param \DateTime $updatedDatetime
     *
     * @return DictCustomerAddress
     */
    public function setUpdatedDatetime($updatedDatetime)
    {
        $this->updatedDatetime = $updatedDatetime;

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
     * Set title
     *
     * @param string $title
     *
     * @return DictCustomerAddress
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return DictCustomerAddress
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set x
     *
     * @param string $x
     *
     * @return DictCustomerAddress
     */
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get x
     *
     * @return string
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param string $y
     *
     * @return DictCustomerAddress
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get y
     *
     * @return string
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set createdBy
     *
     * @param \ApiBundle\Entity\User $createdBy
     *
     * @return DictCustomerAddress
     */
    public function setCreatedBy(\ApiBundle\Entity\User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
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
     * Set updatedBy
     *
     * @param \ApiBundle\Entity\User $updatedBy
     *
     * @return DictCustomerAddress
     */
    public function setUpdatedBy(\ApiBundle\Entity\User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

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
     * Set customer
     *
     * @param \ApiBundle\Entity\User $customer
     *
     * @return DictCustomerAddress
     */
    public function setCustomer(\ApiBundle\Entity\User $customer)
    {
        $this->customer = $customer;

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
     * @var integer
     */
    private $isDisabled;


    /**
     * Set isDisabled
     *
     * @param integer $isDisabled
     *
     * @return DictCustomerAddress
     */
    public function setIsDisabled($isDisabled)
    {
        $this->isDisabled = $isDisabled;

        return $this;
    }

    /**
     * Get isDisabled
     *
     * @return integer
     */
    public function getIsDisabled()
    {
        return $this->isDisabled;
    }
}
