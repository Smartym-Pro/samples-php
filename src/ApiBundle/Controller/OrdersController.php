<?php

namespace ApiBundle\Controller;


use ApiBundle\Controller\ApiDocAnnotations\User;
use ApiBundle\Controller\ApiDocAnnotations\Worker;
use ApiBundle\Controller\ApiDocAnnotations\WorkerPosition;
use ApiBundle\Controller\ApiDocAnnotations\Order;
use ApiBundle\Controller\ApiDocAnnotations\OrderCountersByStatus;
use ApiBundle\Controller\ApiDocAnnotations\OrderPrice;
use ApiBundle\Controller\ApiDocAnnotations\OrderFeedback;
use ApiBundle\Controller\ApiDocAnnotations\Orders;
use ApiBundle\Transformers\OrderChecklistPointCreateTransformer;
use ApiBundle\Transformers\OrderCreateTransformer;
use ApiBundle\Transformers\OrderEstimateCreateTransformer;
use ApiBundle\Transformers\OrderFeedbackCreateTransformer;
use ApiBundle\Transformers\OrderFeedbackReadTransformer;
use ApiBundle\Transformers\OrderReadTransformer;
use ApiBundle\Transformers\OrderStatusCounterReadTransformer;
use ApiBundle\Transformers\OrderStatusCreateTransformer;
use ApiBundle\Transformers\UserReadTransformer;
use DomainBundle\Entity\Orders\OrderChecklistPoint;
use DomainBundle\Entity\Orders\OrderChecklistPointPhoto;
use DomainBundle\Entity\Orders\OrderStatus;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use League\Tactician\Bundle\Middleware\InvalidCommandException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use ServiceBundle\Commands\Order\OrderChecklistPointCreateCommand;
use ServiceBundle\Commands\Order\OrderChecklistPointDeleteCommand;
use ServiceBundle\Commands\Order\OrderChecklistPointPhotoAfterCreateCommand;
use ServiceBundle\Commands\Order\OrderChecklistPointPhotoBeforeCreateCommand;
use ServiceBundle\Commands\Order\OrderChecklistPointPhotoDeleteCommand;
use ServiceBundle\Commands\Order\OrderChecklistPointUpdateCommand;
use ServiceBundle\Commands\Order\OrderCreateCommand;
use ServiceBundle\Commands\Order\OrderEstimateCreateCommand;
use ServiceBundle\Commands\Order\OrderFeedbackCreateCommand;
use ServiceBundle\Commands\Order\OrderReadCommand;
use ServiceBundle\Commands\Order\OrderReadListCommand;
use ServiceBundle\Commands\Order\OrderRecommendedReadListCommand;
use ServiceBundle\Commands\Order\OrderStatusCounterReadCommand;
use ServiceBundle\Commands\Order\OrderStatusCreateCommand;
use ServiceBundle\Commands\Order\OrderUpdateCommand;
use ServiceBundle\Commands\Filesystem\UnzipFileCommand;
use ServiceBundle\Exceptions\Validators\Order\ArrayKeyTitleHaveToBeSetValidatorException;
use ServiceBundle\Exceptions\Validators\Order\ChecklistPointArrayKeyTitleHaveToBeSetValidatorException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrdersController extends FOSRestController
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Returns a list of recommended orders for worker",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Worker id"
     *          },
     *     },
     *     filters={
     *         {
     *             "name"="start_from_timestamp",
     *             "dataType"="string",
     *             "description"="Get orders that starts from timestamp",
     *             "required"=true
     *         },
     *         {
     *             "name"="start_to_timestamp",
     *             "dataType"="string",
     *             "description"="Get orders that ends to timestamp",
     *             "required"=true
     *         },
     *         {
     *             "name"="page",
     *             "dataType"="integer",
     *             "description"="Number of page for pagination",
     *             "default"="1"
     *         },
     *         {
     *             "name"="per_page",
     *             "dataType"="integer",
     *             "description"="Number of items per page",
     *             "default"="1"
     *         },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\Orders",
     *       "groups"="orders_list"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOrdersRecommendedListAction(Request $request)
    {
        $command = new OrderRecommendedReadListCommand(
            array_merge(
                ['worker_id' => (int)$request->get('id')],
                $request->query->all()
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        $orders = $commandBus->handle($command);

        $pagination = $this->get('fractal.paginator')->setPagination($orders, $command->getPage(), $command->getPerPage());

        $data = $this->get('fractal.manager')->toArray($pagination->getPageItems(), new OrderReadTransformer($this->get('doctrine.orm.entity_manager'), $this->get('fos_user.user_manager'), $this->get('translator')), $pagination);

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Returns total number orders for each status",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderStatusCounterData",
     *       "groups"="order_status_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @return Response
     */
    public function getOrderStatusesCountersAction()
    {
        $command = new OrderStatusCounterReadCommand();
        $commandBus = $this->get('tactician.commandbus');
        $orders = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($orders, new OrderStatusCounterReadTransformer());

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Returns Order info",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderBody",
     *       "groups"="order_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     */
    public function getOrderAction($id)
    {
        $command = new OrderReadCommand(['id' => (int)$id]);
        $commandBus = $this->get('tactician.commandbus');
        $order = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($order, new OrderReadTransformer($this->get('doctrine.orm.entity_manager'), $this->get('fos_user.user_manager'), $this->get('translator')));

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Add Order",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     parameters={
     *         {
     *              "name"="customer_id",
     *              "dataType"="integer",
     *              "description"="Manager id that will be assigned to order",
     *              "required"=true
     *          },
     *         {
     *              "name"="manager_id",
     *              "dataType"="integer",
     *              "description"="Manager id that will be assigned to order",
     *              "required"=false
     *          },
     *         {
     *              "name"="address_id",
     *              "dataType"="integer",
     *              "description"="Order address id",
     *              "required"=false
     *          },
     *         {
     *              "name"="status",
     *              "dataType"="string",
     *              "description"="Order status, values in [NEW, NEW_WAITING_PHOTOS]",
     *              "required"=true
     *          },
     *         {
     *              "name"="description",
     *              "dataType"="string",
     *              "description"="Order description",
     *              "required"=false
     *          },
     *         {
     *              "name"="organization",
     *              "dataType"="string",
     *              "description"="Order description",
     *              "required"=false
     *          },
     *         {
     *              "name"="checklist",
     *              "dataType"="array",
     *              "description"="Array with checklist points objects. Example [{'title':'point1', 'uuid':'123'}, ...]",
     *              "required"=false
     *          },
     *         {
     *              "name"="start_timestamp",
     *              "dataType"="string",
     *              "description"="Order start timestamp",
     *              "required"=false
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderBody",
     *       "groups"="order_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws ChecklistPointArrayKeyTitleHaveToBeSetValidatorException
     */
    public function postOrderAction(Request $request)
    {
        $command = new OrderCreateCommand($request->request->all());
        $commandBus = $this->get('tactician.commandbus');
        $order = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($order, new OrderReadTransformer($this->get('doctrine.orm.entity_manager'), $this->get('fos_user.user_manager'), $this->get('translator')));

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Update Order info",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="manager_id",
     *              "dataType"="integer",
     *              "description"="Manager id that will be assigned to order",
     *              "required"=false
     *          },
     *         {
     *              "name"="worker_id",
     *              "dataType"="integer",
     *              "description"="Assign worker to order and order status will be updated to 'worker_added'",
     *              "required"=false
     *          },
     *         {
     *              "name"="address_id",
     *              "dataType"="integer",
     *              "description"="Order address id",
     *              "required"=false
     *          },
     *         {
     *              "name"="description",
     *              "dataType"="string",
     *              "description"="Order description",
     *              "required"=false
     *          },
     *         {
     *              "name"="organization",
     *              "dataType"="string",
     *              "description"="Order description",
     *              "required"=false
     *          },
     *         {
     *              "name"="start_timestamp",
     *              "dataType"="string",
     *              "description"="Order start timestamp",
     *              "required"=false
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderBody",
     *       "groups"="order_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putOrderAction(Request $request)
    {
        $command = new OrderUpdateCommand(
            array_merge(
                ['id' => (int)$request->get('id')],
                $request->request->all()
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        $order = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($order, new OrderReadTransformer($this->get('doctrine.orm.entity_manager'), $this->get('fos_user.user_manager'), $this->get('translator')));

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Add new status to order",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="status",
     *              "dataType"="string",
     *              "description"="New order status. Values [NEW, PRICE_ADDED, APPROVED_BY_CUSTOMER, WORKER_ADDED, WORKER_IN_WAY, WORKER_DOING, WORKER_DONE, CLOSED, CANCELED]",
     *              "required"=true
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderBody",
     *       "groups"="order_status_create"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postOrderStatusAction(Request $request)
    {
        $command = new OrderStatusCreateCommand(array_merge(['order_id' => (int)$request->get('id')], $request->request->all()));
        $commandBus = $this->get('tactician.commandbus');
        /** @var OrderStatus $orderStatus */
        $orderStatus = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray(
            $orderStatus->getOrder(),
            new OrderReadTransformer(
                $this->get('doctrine.orm.entity_manager'),
                $this->get('fos_user.user_manager'),
                $this->get('translator')
            )
        );

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Add new estimation to order and update order status to 'PRICE_ADDED'. Manager have to be set before estimation.",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="start_timestamp",
     *              "dataType"="string",
     *              "description"="Order start timestamp",
     *              "required"=false
     *          },
     *         {
     *              "name"="price_currency_code",
     *              "dataType"="string",
     *              "description"="Currency code for order price. Values have to be in upper case, [USD, EUR]",
     *              "required"=true
     *          },
     *         {
     *              "name"="price_value",
     *              "dataType"="string",
     *              "description"="Price value for order in decimal format with precision 2",
     *              "required"=true
     *          },
     *         {
     *              "name"="work_interval",
     *              "dataType"="string",
     *              "description"="Time in minutes needed to do order.",
     *              "required"=true
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderEstimateData",
     *       "groups"="order_estimate_create"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postOrderEstimateAction(Request $request)
    {
        $command = new OrderEstimateCreateCommand(
            array_merge(
                $request->request->all(),
                [
                    'order_id'    => (int)$request->get('id'),
                    'price_value' => $request->get('price_value')
                ]
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        $order = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($order, new OrderEstimateCreateTransformer());

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Add feedback to order and automaticaly add status 'closed' to order",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="message",
     *              "dataType"="string",
     *              "description"="Feedback text",
     *              "required"=true
     *          },
     *         {
     *              "name"="mark",
     *              "dataType"="string",
     *              "description"="Feedback mark. Values in [1..5]",
     *              "required"=true
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderBody",
     *       "groups"="order_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postOrderFeedbackAction(Request $request)
    {
        $command = new OrderFeedbackCreateCommand(
            array_merge(
                $request->request->all(),
                [
                    'order_id' => (int)$request->get('id')
                ]
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        /** @var \DomainBundle\Entity\Orders\OrderFeedback $feedback */
        $feedback = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($feedback->getOrder(), new OrderReadTransformer($this->get('doctrine.orm.entity_manager'), $this->get('fos_user.user_manager'), $this->get('translator')));

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Update order feedback",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="message",
     *              "dataType"="string",
     *              "description"="Feedback text",
     *              "required"=false
     *          },
     *         {
     *              "name"="mark",
     *              "dataType"="string",
     *              "description"="Feedback mark. Values in [1..5]",
     *              "required"=false
     *          },
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putOrderFeedbackAction(Request $request)
    {
        $orderId = $request->get('id');

        return $this->json((object)[]);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Update Order info",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *         {
     *              "name"="photo_id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Photo id"
     *          },
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteOrderPhotosAction(Request $request)
    {
        $orderId = $request->get('id');

        return $this->json((object)[]);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Add checklist point to order",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order id"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="title",
     *              "dataType"="string",
     *              "description"="Checklist point title",
     *              "required"=true
     *          },
     *         {
     *              "name"="uuid",
     *              "dataType"="string",
     *              "description"="Checklist point uuid, length 20 symbols",
     *              "required"=false
     *          }
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderChecklistPointData",
     *       "groups"="checklist_create"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postOrderChecklistPointAction(Request $request)
    {
        $command = new OrderChecklistPointCreateCommand(
            array_merge(
                $request->request->all(),
                [
                    'order_id' => (int)$request->get('id')
                ]
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        $checklistPoint = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($checklistPoint, new OrderChecklistPointCreateTransformer());

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Update checklist point for order",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Checklist point id"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="title",
     *              "dataType"="string",
     *              "description"="Checklist point title",
     *              "required"=false
     *          },
     *         {
     *              "name"="worker_status",
     *              "dataType"="string",
     *              "description"="Checklist point worker status, values in [DONE, UNDONE]",
     *              "required"=false
     *          },
     *         {
     *              "name"="customer_status",
     *              "dataType"="string",
     *              "description"="Checklist point customer status, values in [APPROVED, DECLINED]",
     *              "required"=false
     *          }
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putOrderChecklistPointAction(Request $request)
    {
        $command = new OrderChecklistPointUpdateCommand(
            array_merge(
                $request->request->all(),
                [
                    'id' => (int)$request->get('id')
                ]
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        $checklistPoint = $commandBus->handle($command);

        return $this->json((object)[]);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Delete checklist point from order",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Checklist point id"
     *          },
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteOrderChecklistPointAction(Request $request)
    {
        $command = new OrderChecklistPointDeleteCommand(['id' => (int)$request->get('id')]);
        $commandBus = $this->get('tactician.commandbus');
        $commandBus->handle($command);

        return $this->json((object)[]);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Add photos to checklist (type BEFORE) and update order status to 'new'",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         },
     *         {
     *             "name"="Content-Type",
     *             "required"="true",
     *             "description"="multipart/form-data; boundary=XXXX"
     *         }
     *     },
     *     parameters={
     *         {
     *              "name"="zip",
     *              "dataType"="file",
     *              "description"="Zip file, max size 70Mb. File includes images with name CHACKLIST_POINT_ID.*.jpg",
     *              "required"=true
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderBody",
     *       "groups"="order_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postOrderChecklistPhotosBeforeAction(Request $request)
    {
        $command = new UnzipFileCommand(
            [
                'zip' => !empty($_FILES['zip'])
                    ? new UploadedFile(
                        $_FILES['zip']['tmp_name'],
                        $_FILES['zip']['name'],
                        $_FILES['zip']['type'],
                        $_FILES['zip']['size'],
                        $_FILES['zip']['error']
                    ) : null
            ]
        );
        $commandBus = $this->get('tactician.commandbus');
        $unzipedFiles = $commandBus->handle($command);

        $command = new OrderChecklistPointPhotoBeforeCreateCommand(
            [
                'files'               => $unzipedFiles
            ]
        );
        /** @var OrderChecklistPoint $orderChecklistPoint */
        $orderChecklistPoint = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($orderChecklistPoint->getOrder(), new OrderReadTransformer($this->get('doctrine.orm.entity_manager'), $this->get('fos_user.user_manager'), $this->get('translator')));

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Add photos to checklist (type After)",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         },
     *         {
     *             "name"="Content-Type",
     *             "required"="true",
     *             "description"="multipart/form-data; boundary=XXXX"
     *         }
     *     },
     *     parameters={
     *         {
     *              "name"="zip",
     *              "dataType"="file",
     *              "description"="Zip file, max size 70Mb. File includes images with name CHACKLIST_POINT_PHOTO_BEFORE_ID.*.jpg",
     *              "required"=true
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\OrderBody",
     *       "groups"="order_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postOrderChecklistPhotosAfterAction(Request $request)
    {
        $command = new UnzipFileCommand(
            [
                'zip' => !empty($_FILES['zip'])
                    ? new UploadedFile(
                        $_FILES['zip']['tmp_name'],
                        $_FILES['zip']['name'],
                        $_FILES['zip']['type'],
                        $_FILES['zip']['size'],
                        $_FILES['zip']['error']
                    ) : null
            ]
        );
        $commandBus = $this->get('tactician.commandbus');
        $unzipedFiles = $commandBus->handle($command);

        $command = new OrderChecklistPointPhotoAfterCreateCommand(
            [
                'files'                     => $unzipedFiles
            ]
        );
        /** @var OrderChecklistPoint $orderChecklistPoint */
        $orderChecklistPoint = $commandBus->handle($command);
        $data = $this->get('fractal.manager')->toArray($orderChecklistPoint->getOrder(), new OrderReadTransformer($this->get('doctrine.orm.entity_manager'), $this->get('fos_user.user_manager'), $this->get('translator')));

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations with orders.",
     *     description="Delete photo from checklist point",
     *     headers={
     *         {
     *             "name"="Authorization",
     *             "required"="true",
     *             "description"="OAuth access token: value 'Bearer ACCESS_TOKEN'"
     *         }
     *     },
     *     requirements={
     *         {
     *              "name"="id",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="Order checklist point photo id"
     *          },
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteOrderChecklistPhotoAction(Request $request)
    {
        $command = new OrderChecklistPointPhotoDeleteCommand(['id' => (int)$request->get('id')]);
        $commandBus = $this->get('tactician.commandbus');
        $commandBus->handle($command);

        return $this->json((object)[]);
    }
}