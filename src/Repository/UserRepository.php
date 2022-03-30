<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
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
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }
    public function findByRole(string $role)
    {
        $role = mb_strtoupper($role);

        return $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', '"' . $role . '"')
            ->getQuery()
            ->getResult();
    }

    public function findByCoach(string $query)
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->like('u.nom', ':query'),
                        $qb->expr()->like('u.prenom', ':query'),
                        $qb->expr()->like('u.description', ':query'),
                        $qb->expr()->like('u.ville', ':query'),
                        $qb->expr()->like('u.coaching', ':query'),
                    ),
                )
            )
            ->setParameter('query', '%' . $query . '%')
        ;
        return $qb
            ->getQuery()
            ->getResult();
    }

    // public function findByProgramme(User $user, string $query)
    // {
    //     $programme = $user->getProgramme();
    //     $qb = $this->createQueryBuilder('p');
    //     $qb
    //         ->where(
    //             $qb->expr()->andX(
    //                 $qb->expr()->orX(
    //                     $qb->expr()->like('p.nom', ':query'),
    //                     $qb->expr()->like('p.description', ':query'),
    //                 ),
    //             )
    //         )
    //         ->setParameter('query', '%' . $query . '%')
    //     ;
    //     return $qb
    //         ->getQuery()
    //         ->getResult();
    // }

    // public function findByName(string $query)
    // {
    //     return $this->createQueryBuilder('u')
    //         ->andWhere('u.nom = :val')
    //         ->setParameter('val', $query)
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }

    public function findCoachs(string $role, String $ville = null, String $coaching = null)
    {
        $role = mb_strtoupper($role);

        $query = $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', '"' . $role . '"');

            if($ville != null)
            {
                $query->andWhere('u.ville = :val')
                ->setParameter('val', $ville);
            }

            if($coaching != null)
            {
                $query->andWhere('u.coaching = :val')
                ->setParameter('val', $coaching);
            }

            return $query->getQuery()
            ->getResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}