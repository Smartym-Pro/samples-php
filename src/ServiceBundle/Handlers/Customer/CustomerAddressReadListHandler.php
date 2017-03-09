<?php

namespace ServiceBundle\Handlers\Customer;

use ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DomainBundle\Entity\Customer\DictCustomerAddress;
use FOS\UserBundle\Doctrine\UserManager;
use ServiceBundle\Commands\Customer\CustomerAddressReadListCommand;
use ServiceBundle\Exceptions\Handlers\CustomerIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\UserIsNotCustomerHandlerException;
use ServiceBundle\Security\CustomerAddressVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class CustomerAddressReadListHandler
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
     * @var UserManager
     */
    protected $userManager;


    /**
     * CustomerAddressReadListHandler constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityRepository $dictCustomerAddressRepository
     * @param UserManager $userManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityRepository $dictCustomerAddressRepository,
        UserManager $userManager
    )
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->dictCustomerAddressRepository = $dictCustomerAddressRepository;
        $this->userManager = $userManager;
    }


    /**
     * @param CustomerAddressReadListCommand $command
     * @return array
     * @throws CustomerIsNotExistHandlerException
     * @throws UserIsNotCustomerHandlerException
     */
    public function handle(CustomerAddressReadListCommand $command)
    {
        $customer = $this
            ->userManager
            ->findUserBy(['id' => $command->getCustomerId()]);
        /**
         * @var User $customer
         */
        if (empty($customer)) {
            throw new CustomerIsNotExistHandlerException();
        }
        if (!$customer->hasRole(User::ROLE_CUSTOMER)) {
            throw new UserIsNotCustomerHandlerException();
        }

        $addresses = $customer
            ->getAddresses()
            ->filter(
                function ($entity) {
                    /** @var DictCustomerAddress $entity */
                    return !$entity->getIsDisabled();
                }
            );
        if (!empty($addresses->toArray()) && !$this->authorizationChecker->isGranted(CustomerAddressVoter::VIEW, $addresses->first())) {
            throw new NoAccessToCreateCustomerAddressHandlerException();
        }

        return $addresses->toArray();
    }
}