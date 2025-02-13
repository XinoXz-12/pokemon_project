<?php

namespace App\Repository;

use App\Entity\Battle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Battle>
 */
class BattleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Battle::class);
    }

    /**
     * @return Battle[] Returns an array of Battle objects
     */
    public function myBattles($trainer): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.trainer = :id')
            ->setParameter('id', $trainer)
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
