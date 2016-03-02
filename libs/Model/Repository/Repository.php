<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\Caching\IStorage;

abstract class Repository
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * CompetitionRepository constructor.
     * @param EntityManager $em
     * @param IStorage      $cache
     */
    public function __construct(EntityManager $em)
    {
        $this->em         = $em;
        $this->repository = $em->getRepository($this->getEntityClass());
    }

    /**
     * @return EntityRepository
     */
    public function repository()
    {
        return $this->repository;
    }

    /**
     * @return EntityManager
     */
    public function entityManager()
    {
        return $this->em;
    }

    /**
     * @return string class
     */
    abstract protected function getEntityClass();
}
