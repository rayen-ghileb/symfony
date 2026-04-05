<?php

namespace App\Repository;

use App\Entity\Reaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    public function findByPost(int $postId): array
    {
        return $this->createQueryBuilder('r')
            ->where('IDENTITY(r.post) = :postId')
            ->setParameter('postId', $postId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByPostAndUser(int $postId, int $userId): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->where('IDENTITY(r.post) = :postId')
            ->andWhere('IDENTITY(r.user) = :userId')   // was r.userId — WRONG
            ->setParameter('postId', $postId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
