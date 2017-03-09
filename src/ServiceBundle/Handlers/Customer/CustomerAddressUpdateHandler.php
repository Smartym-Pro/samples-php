<?php

namespace ServiceBundle\Handlers\Customer;

use ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DomainBundle\Entity\Customer\DictCustomerAddress;
use FOS\UserBundle\Doctrine\UserManager;
use ServiceBundle\Commands\Customer\CustomerAddressUpdateCommand;
use ServiceBundle\Exceptions\Handlers\AddressIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToCreateCustomerAddressHandlerException;
use ServiceBundle\Security\CustomerAddressVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class CustomerAddressUpdateHandler
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


    public function handle(CustomerAddressUpdateCommand $command)
    {
        $address = $this->dictCustomerAddressRepository->findOneBy(['id' => $command->getId()]);
        /** @var DictCustomerAddress $address */
        if (empty($address)) {
            throw new AddressIsNotExistHandlerException();
        }

        $address->setUpdatedBy($this->tokenUser);
        $address->setUpdatedDatetime(new \DateTime());

        if ($command->getTitle()) {
            $address->setTitle($command->getTitle());
        }

        if ($command->getIsDisabled() !== null) {
            $address->setIsDisabled($command->getIsDisabled());
        }
        if ($command->getAddress()) {
            $address->setAddress($command->getAddress());
        }
        if ($command->getX()) {
            $address->setX($command->getX());
        }
        if ($command->getY()) {
            $address->setY($command->getY());
        }

        if (!$this->authorizationChecker->isGranted(CustomerAddressVoter::UPDATE, $address)) {
            throw new NoAccessToCreateCustomerAddressHandlerException();
        }

        $this->em->persist($address);

        return $address;
    }
}