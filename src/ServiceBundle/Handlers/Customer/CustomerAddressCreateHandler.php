<?php

namespace ServiceBundle\Handlers\Customer;

use ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DomainBundle\Entity\Customer\DictCustomerAddress;
use FOS\UserBundle\Doctrine\UserManager;
use ServiceBundle\Commands\Customer\CustomerAddressCreateCommand;
use ServiceBundle\Exceptions\Handlers\CustomerIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToCreateCustomerAddressHandlerException;
use ServiceBundle\Exceptions\Handlers\UserIsNotCustomerHandlerException;
use ServiceBundle\Security\CustomerAddressVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class CustomerAddressCreateHandler
{
    /**
     * @var User
     */
    protected $tokenUser;

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



    public function __construct(
        User $tokenUser,
        EntityManager $em,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityRepository $dictCustomerAddressRepository,
        UserManager $userManager
    ) {
        $this->em = $em;
        $this->tokenUser = $tokenUser;
        $this->authorizationChecker = $authorizationChecker;
        $this->dictCustomerAddressRepository = $dictCustomerAddressRepository;
        $this->userManager = $userManager;
    }


    public function handle(CustomerAddressCreateCommand $command)
    {
        $customer = $this->userManager->findUserBy(['id' => $command->getCustomerId()]);
        /**
         * @var User $customer
         */
        if (empty($customer)) {
            throw new CustomerIsNotExistHandlerException();
        }
        if (!$customer->hasRole(User::ROLE_CUSTOMER)) {
            throw new UserIsNotCustomerHandlerException();
        }

        $class = $this->dictCustomerAddressRepository->getClassName();
        $address = new $class;
        /**
         * @var DictCustomerAddress $address
         */
        $address->setCreatedBy($this->tokenUser);
        $address->setCreatedDatetime(new \DateTime());
        $address->setUpdatedBy($this->tokenUser);
        $address->setUpdatedDatetime(new \DateTime());
        $address->setCustomer($customer);
        $address->setTitle($command->getTitle());
        $address->setAddress($command->getAddress());
        $address->setX($command->getX());
        $address->setY($command->getY());

        if (!$this->authorizationChecker->isGranted(CustomerAddressVoter::CREATE, $address)) {
            throw new NoAccessToCreateCustomerAddressHandlerException();
        }

        $this->em->persist($address);

        return $address;
    }
}