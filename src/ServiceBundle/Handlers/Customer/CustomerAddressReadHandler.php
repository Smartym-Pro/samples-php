<?php

namespace ServiceBundle\Handlers\Customer;

use ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use ServiceBundle\Commands\Customer\CustomerAddressReadCommand;
use ServiceBundle\Exceptions\Handlers\AddressIsNotExistHandlerException;
use ServiceBundle\Security\CustomerAddressVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class CustomerAddressReadHandler
{
    /**
     * @var EntityRepository
     */
    protected $dictCustomerAddressRepository;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;


    /**
     * CustomerAddressReadHandler constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityRepository $dictCustomerAddressRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityRepository $dictCustomerAddressRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->dictCustomerAddressRepository = $dictCustomerAddressRepository;
    }


    /**
     * @param CustomerAddressReadCommand $command
     * @return null|object
     * @throws AddressIsNotExistHandlerException
     */
    public function handle(CustomerAddressReadCommand $command)
    {
        $address = $this->dictCustomerAddressRepository->findOneBy(['id' => $command->getId()]);
        /**
         * @var User $customer
         */
        if (empty($address)) {
            throw new AddressIsNotExistHandlerException();
        }

        if (!$this->authorizationChecker->isGranted(CustomerAddressVoter::VIEW, $address)) {
            throw new NoAccessToCreateCustomerAddressHandlerException();
        }

        return $address;
    }
}