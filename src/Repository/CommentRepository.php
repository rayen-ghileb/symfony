<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /** Only top-level, non-deleted comments for a given post */
    public function findTopLevelByPost(int $postId): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.replies', 'r')->addSelect('r')
            ->where('c.post = :postId')
            ->andWhere('c.parentComment IS NULL')
            ->andWhere('c.deleted = false')
            ->setParameter('postId', $postId)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
}