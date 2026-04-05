<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /** Eager-load media and reactions to avoid N+1 queries on list page */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.mediaList', 'm')->addSelect('m')
            ->leftJoin('p.reactions', 'r')->addSelect('r')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}