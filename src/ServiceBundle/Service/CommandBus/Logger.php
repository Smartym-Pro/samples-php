<?php

namespace ServiceBundle\Service\CommandBus;


use Doctrine\ORM\EntityManager;
use DomainBundle\Entity\Audit;
use ServiceBundle\Model\EntityRepository;

class Logger
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $em;

    protected $sc;

    public function __construct(EntityManager $em, EntityRepository $repository, $sc)
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->sc = $sc;
    }

    public function log($info)
    {
        $log = $this->repository->create();
        /**
         * @var Audit $log
         */
        $log->setCreatedDatetime(new \DateTime());
        $created_by = !empty($this->sc->getToken()) && !empty($this->sc->getToken()->getUser()) ? $this->sc->getToken()->getUser()->getId() : null;
        $log->setCreatedBy($created_by);
        $log->setMessage($info);

        $this->em->persist($log);
        $this->em->flush();
    }
}