<?php

namespace Tests\ServiceBundle\Handlers\Order;

use ApiBundle\Entity\User;
use ApiBundle\Service\UserManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DomainBundle\Entity\Customer\DictCustomerAddress;
use DomainBundle\Entity\Orders\Order;
use ServiceBundle\Commands\Order\OrderCreateCommand;
use DomainBundle\Entity\Orders\OrderStatus;
use ServiceBundle\Handlers\Order\OrderCreateHandler;
use ServiceBundle\Model\Repository\OrderChecklistPointRepository;
use ServiceBundle\Model\Repository\OrderRepository;
use ServiceBundle\Model\Repository\OrderStatusRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OrderCreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \ServiceBundle\Handlers\Order\OrderCreateHandler::<public>
     */
    public function testCreateOrderWithAllFields()
    {
        $description = 'description';
        $organization = 'Organization';
        $customerId = 10;
        $managerId = 1;
        $command = $this->getMockBuilder(OrderCreateCommand::class)
            ->disableOriginalConstructor()
            ->getMock();
        $command->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $command->method('getManagerId')
            ->will($this->returnValue($managerId));
        $command->method('getDescription')
            ->will($this->returnValue($description));
        $command->method('getOrganization')
            ->will($this->returnValue($organization));
        $command->method('getStartTimestamp')
            ->will($this->returnValue(time()));
        $command->method('getAddressId')
            ->will($this->returnValue(15));
        $command->method('getStatus')
            ->will($this->returnValue(OrderStatus::STATUS_NEW));
        $command->method('getChecklist')
            ->will($this->returnValue([['title'=>'test point', 'uuid' => '123']]));


        $sessionUserRole = User::ROLE_CUSTOMER;
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rolesMap = [
            [User::ROLE_SUPER_ADMIN, User::ROLE_SUPER_ADMIN === $sessionUserRole],
            [User::ROLE_ADMIN, User::ROLE_ADMIN === $sessionUserRole],
            [User::ROLE_MANAGER, User::ROLE_MANAGER === $sessionUserRole],
            [User::ROLE_WORKER, User::ROLE_WORKER === $sessionUserRole],
            [User::ROLE_CUSTOMER, User::ROLE_CUSTOMER === $sessionUserRole],
        ];
        $user->method('hasRole')
            ->will($this->returnValueMap($rolesMap));

        $orderRepository = $this
            ->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->method('getClassName')
            ->will($this->returnValue(Order::class));

        $orderChecklistPointRepository = $this
            ->getMockBuilder(OrderChecklistPointRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderChecklistPointRepository->method('createChecklistPoint');

        $orderStatusRepository = $this
            ->getMockBuilder(OrderStatusRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatusRepository->method('getClassName')
            ->will($this->returnValue(OrderStatus::class));

        $address = $this->getMockBuilder(DictCustomerAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dictCustomerAddressRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dictCustomerAddressRepository->method('findOneBy')
            ->will($this->returnValue($address));


        $customer = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rolesMap = [
            [User::ROLE_SUPER_ADMIN, false],
            [User::ROLE_ADMIN, false],
            [User::ROLE_MANAGER, false],
            [User::ROLE_WORKER, false],
            [User::ROLE_CUSTOMER, true],
        ];
        $customer->method('hasRole')
            ->will($this->returnValueMap($rolesMap));

        $manager = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rolesMap = [
            [User::ROLE_SUPER_ADMIN, false],
            [User::ROLE_ADMIN, false],
            [User::ROLE_MANAGER, true],
            [User::ROLE_WORKER, false],
            [User::ROLE_CUSTOMER, false],
        ];
        $manager->method('hasRole')
            ->will($this->returnValueMap($rolesMap));


        $userManager = $this
            ->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usersMap = [
            [['id'=> $customerId], $customer],
            [['id'=> $managerId], $manager]
        ];
        $userManager->method('findUserBy')
            ->will($this->returnValueMap($usersMap));

        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->exactly(2))
            ->method('persist');

        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationChecker->method('isGranted')
            ->will($this->returnValue(true));


        $handler = new OrderCreateHandler(
            $user,
            $entityManager,
            $orderRepository,
            $orderStatusRepository,
            $orderChecklistPointRepository,
            $dictCustomerAddressRepository,
            $userManager,
            $authorizationChecker
        );
        $order = $handler->handle($command);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($user, $order->getCreatedBy());
        $this->assertInstanceOf(\DateTime::class, $order->getCreatedDatetime());
        $this->assertEquals($user, $order->getUpdatedBy());
        $this->assertInstanceOf(\DateTime::class, $order->getUpdatedDatetime());
        $this->assertEquals($customer, $order->getCustomer());
        $this->assertEquals($manager, $order->getManager());
        $this->assertEquals($description, $order->getDescription());
        $this->assertEquals($organization, $order->getOrganization());
        $this->assertInstanceOf(\DateTime::class, $order->getStartDatetime());
        $this->assertInstanceOf(OrderStatus::class, $order->getStatuses()->first());
        $this->assertEquals($address, $order->getAddress());
    }
}
