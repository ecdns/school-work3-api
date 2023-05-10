<?php

declare(strict_types=1);

namespace Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Exception;

class DAO
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addEntity($entity): void
    {
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new Exception($e->getMessage());
        }

    }

    public function deleteEntity($entity): void
    {
        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        } catch (ORMException|ORMInvalidArgumentException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateEntity($entity = null): void
    {
        try {
            $this->entityManager->flush($entity);
        } catch (OptimisticLockException|ORMException|Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getOneEntity($entity, $id)
    {
        try {
            return $this->entityManager->find($entity, $id);
        } catch (ORMException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getAllEntities($entity): array
    {
        try {
            return $this->entityManager->getRepository($entity)->findAll();
        } catch (ORMException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getEntitiesBy($entity, $criteria): array
    {
        try {
            return $this->entityManager->getRepository($entity)->findBy($criteria);
        } catch (ORMException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getOneEntityBy($entity, $criteria)
    {
        try {
            return $this->entityManager->getRepository($entity)->findOneBy($criteria);
        } catch (ORMException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getEntityByOrder($entity, $criteria, $order): array
    {
        try {
            return $this->entityManager->getRepository($entity)->findBy($criteria, $order);
        } catch (ORMException $e) {
            throw new Exception($e->getMessage());
        }
    }
}