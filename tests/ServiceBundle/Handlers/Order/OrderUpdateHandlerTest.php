<?php

namespace Tests\ServiceBundle\Handlers\Order;

use ApiBundle\Entity\User;
use ApiBundle\Service\UserManager;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use DomainBundle\Entity\Customer\DictCustomerAddress;
use DomainBundle\Entity\Orders\Order;
use DomainBundle\Entity\Orders\OrderEstimate;
use ServiceBundle\Commands\Order\OrderUpdateCommand;
use ServiceBundle\Handlers\Order\OrderUpdateHandler;
use ServiceBundle\Model\Repository\OrderRepository;
use ServiceBundle\Model\Repository\OrderStatusRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class OrderUpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \ServiceBundle\Handlers\Order\OrderUpdateHandler::<public>
     */
    public function testAssignManagerToNewOrder()
    {
        $managerId = 1;
        $command = $this->getMockBuilder(OrderUpdateCommand::class)
            ->disableOriginalConstructor()
            ->getMock();
        $command->method('getManagerId')
            ->will($this->returnValue($managerId));

        $order = new Order();
        $orderRepository = $this
            ->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->method('findOneBy')
            ->will($this->returnValue($order));

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
            [['id'=> $managerId], $manager]
        ];
        $userManager->method('findUserBy')
            ->will($this->returnValueMap($usersMap));

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderStatusRepository = $this
            ->getMockBuilder(OrderStatusRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationChecker->method('isGranted')
            ->will($this->returnValue(true));

        $dictCustomerAddressRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();


        $handler = new OrderUpdateHandler(
            $user,
            $entityManager,
            $orderRepository,
            $orderStatusRepository,
            $userManager,
            $authorizationChecker,
            $dictCustomerAddressRepository
        );
        $order = $handler->handle($command);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(null, $order->getCreatedBy());
        $this->assertEquals(null, $order->getCreatedDatetime());
        $this->assertEquals(null, $order->getUpdatedBy());
        $this->assertEquals(null, $order->getUpdatedDatetime());
        $this->assertEquals(null, $order->getCustomer());
        $this->assertEquals(null, $order->getWorker());
        $this->assertEquals($manager, $order->getManager());
        $this->assertEquals(null, $order->getDescription());
        $this->assertEquals(null, $order->getOrganization());
        $this->assertEquals(null, $order->getStartDatetime());
        $this->assertEquals([], $order->getStatuses()->toArray());
        $this->assertEquals([], $order->getEstimates()->toArray());
        $this->assertEquals([], $order->getChecklist()->toArray());
        $this->assertEquals(null, $order->getAddress());
    }

    /**
     * @covers \ServiceBundle\Handlers\Order\OrderUpdateHandler::<public>
     */
    public function testAssignWorkerToOrder()
    {
        $workerId = 1;
        $command = $this->getMockBuilder(OrderUpdateCommand::class)
            ->disableOriginalConstructor()
            ->getMock();
        $command->method('getWorkerId')
            ->will($this->returnValue($workerId));

        $order = new Order();
        $order->setStartDatetime(New \DateTime());
        $estimate = new OrderEstimate();
        $estimate->setWorkInterval(new \DateTime('2017-02-01 01:00:00'));
        $order->addEstimate($estimate);

        $orderRepository = $this
            ->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->method('findOneBy')
            ->will($this->returnValue($order));

        $worker = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $worker->method('getId')
            ->will($this->returnValue($workerId));
        $rolesMap = [
            [User::ROLE_SUPER_ADMIN, false],
            [User::ROLE_ADMIN, false],
            [User::ROLE_MANAGER, false],
            [User::ROLE_WORKER, true],
            [User::ROLE_CUSTOMER, false],
        ];
        $worker->method('hasRole')
            ->will($this->returnValueMap($rolesMap));


        $userManager = $this
            ->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usersMap = [
            [['id'=> $workerId], $worker]
        ];
        $userManager->method('findUserBy')
            ->will($this->returnValueMap($usersMap));

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderStatusRepository = $this
            ->getMockBuilder(OrderStatusRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatusRepository->method('setOrderStatus');

        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationChecker->method('isGranted')
            ->will($this->returnValue(true));

        $dictCustomerAddressRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();



        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->method('getQueryBuilderFindBy')
            ->will($this->returnValue($queryBuilder));
        $orderRepository->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $queryBuilder->method('select')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->method('leftJoin')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->method('where')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->method('andWhere')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->method('getDQL')
            ->will($this->returnValue(''));
        $queryBuilder->method('setParameter')
            ->will($this->returnValue($queryBuilder));
        $expr = new Expr();
        $queryBuilder->method('expr')
            ->will($this->returnValue($expr));
        $getQuery = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(array('getArrayResult'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryBuilder->method('getQuery')
            ->will($this->returnValue($getQuery));


        $handler = new OrderUpdateHandler(
            $user,
            $entityManager,
            $orderRepository,
            $orderStatusRepository,
            $userManager,
            $authorizationChecker,
            $dictCustomerAddressRepository
        );
        $order = $handler->handle($command);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(null, $order->getCreatedBy());
        $this->assertEquals(null, $order->getCreatedDatetime());
        $this->assertEquals(null, $order->getUpdatedBy());
        $this->assertEquals(null, $order->getUpdatedDatetime());
        $this->assertEquals(null, $order->getCustomer());
        $this->assertEquals(null, $order->getManager());
        $this->assertEquals($worker, $order->getWorker());
        $this->assertEquals(null, $order->getDescription());
        $this->assertEquals(null, $order->getOrganization());
        $this->assertInstanceOf(\DateTime::class, $order->getStartDatetime());
        $this->assertEquals([], $order->getStatuses()->toArray());
        $this->assertEquals($estimate, $order->getEstimates()->first());
        $this->assertEquals([], $order->getChecklist()->toArray());
        $this->assertEquals(null, $order->getAddress());
    }

    /**
     * @covers \ServiceBundle\Handlers\Order\OrderUpdateHandler::<public>
     */
    public function testSomeFieldsUpdateForNewOrder()
    {
        $addressId = 1;
        $description = 'description';
        $organization = 'Organization';
        $startTimestamp = time();
        $command = $this->getMockBuilder(OrderUpdateCommand::class)
            ->disableOriginalConstructor()
            ->getMock();
        $command->method('getAddressId')
            ->will($this->returnValue($addressId));
        $command->method('getDescription')
            ->will($this->returnValue($description));
        $command->method('getOrganization')
            ->will($this->returnValue($organization));
        $command->method('getStartTimestamp')
            ->will($this->returnValue($startTimestamp));

        $order = new Order();

        $orderRepository = $this
            ->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->method('findOneBy')
            ->will($this->returnValue($order));


        $userManager = $this
            ->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderStatusRepository = $this
            ->getMockBuilder(OrderStatusRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatusRepository->method('setOrderStatus');

        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationChecker->method('isGranted')
            ->will($this->returnValue(true));

        $address = new DictCustomerAddress();
        $dictCustomerAddressRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dictCustomerAddressRepository->method('findOneBy')
            ->will($this->returnValue($address));


        $handler = new OrderUpdateHandler(
            $user,
            $entityManager,
            $orderRepository,
            $orderStatusRepository,
            $userManager,
            $authorizationChecker,
            $dictCustomerAddressRepository
        );
        $order = $handler->handle($command);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(null, $order->getCreatedBy());
        $this->assertEquals(null, $order->getCreatedDatetime());
        $this->assertEquals(null, $order->getUpdatedBy());
        $this->assertEquals(null, $order->getUpdatedDatetime());
        $this->assertEquals(null, $order->getCustomer());
        $this->assertEquals(null, $order->getManager());
        $this->assertEquals(null, $order->getWorker());
        $this->assertEquals($description, $order->getDescription());
        $this->assertEquals($organization, $order->getOrganization());
        $this->assertEquals(new \DateTime('@' . $startTimestamp), $order->getStartDatetime());
        $this->assertEquals([], $order->getStatuses()->toArray());
        $this->assertEquals([], $order->getEstimates()->toArray());
        $this->assertEquals([], $order->getChecklist()->toArray());
        $this->assertEquals($address, $order->getAddress());
    }
}
