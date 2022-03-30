<?php

namespace App\Repository;

use App\Entity\Disponibilite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Disponibilite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Disponibilite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Disponibilite[]    findAll()
 * @method Disponibilite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DisponibiliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Disponibilite::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Disponibilite $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Disponibilite $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    public function findByUser($user)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.user= :val')
            ->setParameter('val', $user)
            ->getQuery()
            ->getResult()
        ;
    }
    // public function findByCoach($coach)
    // {
    //     return $this->createQueryBuilder('d')
    //         ->andWhere('d.coach_id = :val')
    //         ->setParameter('val', $coach)
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }
    

    /*
    public function findOneBySomeField($value): ?Disponibilite
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
