<?php

namespace ApiBundle\Controller;

use ApiBundle\Controller\ApiDocAnnotations\Users;
use ApiBundle\Controller\ApiDocAnnotations\User;
use ApiBundle\Controller\ApiDocAnnotations\Worker;
use ApiBundle\Controller\ApiDocAnnotations\WorkerPosition;
use ApiBundle\Controller\ApiDocAnnotations\Workers;
use ApiBundle\Transformers\CustomerAddressCreateTransformer;
use ApiBundle\Transformers\CustomerAddressReadTransformer;
use ApiBundle\Transformers\DictWorkerPositionReadTransformer;
use ApiBundle\Transformers\WorkerReadTransformer;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use ServiceBundle\Commands\Customer\CustomerAddressCreateCommand;
use ServiceBundle\Commands\Customer\CustomerAddressReadCommand;
use ServiceBundle\Commands\Customer\CustomerAddressReadListCommand;
use ServiceBundle\Commands\Customer\CustomerAddressUpdateCommand;
use ServiceBundle\Commands\Worker\DictWorkerPositionReadListCommand;
use ServiceBundle\Commands\Worker\WorkerPositionCreateCommand;
use ServiceBundle\Commands\Worker\WorkerPositionDeleteCommand;
use ServiceBundle\Commands\Worker\WorkerReadCommand;
use ServiceBundle\Commands\Worker\WorkerReadListCommand;
use ServiceBundle\Commands\Worker\WorkerUpdateCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends FOSRestController
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations on customers.",
     *     description="Returns a list of customer addresses",
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
     *              "description"="Customer ID"
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\CustomerAddressListData",
     *       "groups"="customer_address_list"
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
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAddressListAction(Request $request)
    {
        $command = new CustomerAddressReadListCommand(
            [
                'customer_id' => (int)$request->get('id'),
            ]
        );
        $commandBus = $this->get('tactician.commandbus');
        $addresses = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($addresses, new CustomerAddressReadTransformer());

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations on customers.",
     *     description="Return customer address info",
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
     *              "description"="Address ID"
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\CustomerAddressData",
     *       "groups"="customer_address_info"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     */
    public function getAddressAction($id)
    {
        $command = new CustomerAddressReadCommand(
            [
                'id' => (int)$id,
            ]
        );
        $commandBus = $this->get('tactician.commandbus');
        $addresses = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($addresses, new CustomerAddressReadTransformer());

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations on customers.",
     *     description="Update customer address",
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
     *              "description"="Address ID"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="title",
     *              "dataType"="string",
     *              "description"="Title for address",
     *              "required"=false
     *          },
     *         {
     *              "name"="address",
     *              "dataType"="string",
     *              "description"="Address",
     *              "required"=false
     *          },
     *         {
     *              "name"="is_disabled",
     *              "dataType"="string",
     *              "description"="Visibility of address. values [0,1]",
     *              "required"=false
     *          },
     *         {
     *              "name"="x",
     *              "dataType"="string",
     *              "description"="Geographic longitude of address",
     *              "required"=false
     *          },
     *         {
     *              "name"="y",
     *              "dataType"="string",
     *              "description"="Geographic latitude of address",
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
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putAddressAction(Request $request)
    {
        $command = new CustomerAddressUpdateCommand(
            array_merge(
                $request->request->all(),
                [
                    'id' => (int)$request->get('id'),
                ]
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        $address = $commandBus->handle($command);

        return $this->json((object)[]);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations on customers.",
     *     description="Add address to customers",
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
     *              "description"="Customer ID"
     *          },
     *     },
     *     parameters={
     *         {
     *              "name"="title",
     *              "dataType"="string",
     *              "description"="Title for address",
     *              "required"=true
     *          },
     *         {
     *              "name"="address",
     *              "dataType"="string",
     *              "description"="Address",
     *              "required"=true
     *          },
     *         {
     *              "name"="x",
     *              "dataType"="string",
     *              "description"="Geographic longitude of address",
     *              "required"=true
     *          },
     *         {
     *              "name"="y",
     *              "dataType"="string",
     *              "description"="Geographic latitude of address",
     *              "required"=true
     *          },
     *     },
     *     output={
     *       "class"="ApiBundle\Controller\ApiDocAnnotations\CustomerAddressData",
     *       "groups"="customer_address_create"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAddressAction(Request $request)
    {
        $command = new CustomerAddressCreateCommand(
            array_merge(
                $request->request->all(),
                [
                    'customer_id' => (int)$request->get('id'),
                ]
            )
        );
        $commandBus = $this->get('tactician.commandbus');
        $address = $commandBus->handle($command);

        $data = $this->get('fractal.manager')->toArray($address, new CustomerAddressCreateTransformer());

        return $this->json($data);
    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations on customers.",
     *     description="Delete customer address",
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
     *              "description"="Address id"
     *          },
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Bad request",
     *         401="Unauthorized",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAddressAction(Request $request)
    {
        return $this->json((object)[]);
    }
}