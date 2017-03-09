<?php

namespace ApiBundle\Controller;

use ApiBundle\Transformers\UserGetCodeTransformer;
use ServiceBundle\Commands\User\UserGetCodeCommand;
use FOS\RestBundle\Controller\FOSRestController;
use League\Tactician\CommandBus;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends FOSRestController
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Auth operations",
     *     description="Register new user and get SMS password to get access token through OAuth",
     *     parameters={

     *         {
     *             "name"="phone_n",
     *             "dataType"="string",
     *             "description"="Phone number",
     *              "required"=true
     *         },
     *         {
     *             "name"="app",
     *             "dataType"="string",
     *             "description"="Application, values in [CUSTOMER, WORKER, ADMIN]",
     *              "required"=true
     *         }
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400={
     *              "Phone number is busy",
     *              "Phone number is needed",
     *              "Other Bad request"
     *          }
     *     }
     * )
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCodeAction(Request $request)
    {
        $command = new UserGetCodeCommand($request->request->all());
        $commandBus = $this->get('tactician.commandbus');
        $user = $commandBus->handle($command);

        /**
         * @var UserEntity $user
         */
        $data = $this->get('fractal.manager')->toArray($user, new UserGetCodeTransformer());

        return $this->json($data);
    }
}
