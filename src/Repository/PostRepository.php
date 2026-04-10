<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Main feed query: filter by visibility, search keyword, sort by date.
     * Uses QueryBuilder + DQL-style joins for eager loading.
     *
     * @param User   $currentUser  Used to enforce PATIENTS_ONLY visibility rule
     * @param string|null $filter  'PUBLIC' | 'PATIENTS_ONLY' | null (all)
     * @param string|null $search  keyword to match against post content
     * @param string $sort         'newest' (default) | 'oldest'
     */
    public function findForFeed(
        User $currentUser,
        ?string $filter = null,
        ?string $search = null,
        string $sort = 'newest'
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.author',    'a')->addSelect('a')
            ->leftJoin('p.mediaList', 'm')->addSelect('m')
            ->leftJoin('p.reactions', 'r')->addSelect('r')
            ->leftJoin('r.user',      'ru')->addSelect('ru')
            ->leftJoin('p.comments',  'c',
                'WITH', 'c.deleted = false AND c.parentComment IS NULL'
            )->addSelect('c')
            ->leftJoin('c.author',    'ca')->addSelect('ca')
            ->leftJoin('c.replies',   'rep',
                'WITH', 'rep.deleted = false'
            )->addSelect('rep')
            ->leftJoin('rep.author',  'repa')->addSelect('repa')
            ->where('p.deleted = false');

        // ── Visibility enforcement ──────────────────────────────────────
        // Patients can see PUBLIC + PATIENTS_ONLY.
        // Psychiatrists and Admins can only see PUBLIC posts
        // (PATIENTS_ONLY is for patients).
        if ($currentUser->getRole() !== 'PATIENT') {
            $qb->andWhere('p.visibility = :pub')
               ->setParameter('pub', Post::VISIBILITY_PUBLIC);
        }

        // ── Visibility filter (user-selected) ──────────────────────────
        if ($filter === Post::VISIBILITY_PUBLIC || $filter === Post::VISIBILITY_PATIENTS_ONLY) {
            // Only allow PATIENTS_ONLY filter for patients
            if ($filter === Post::VISIBILITY_PATIENTS_ONLY && $currentUser->getRole() !== 'PATIENT') {
                $filter = Post::VISIBILITY_PUBLIC;
            }
            $qb->andWhere('p.visibility = :visibility')
               ->setParameter('visibility', $filter);
        }

        // ── Keyword search (DQL LIKE on content) ───────────────────────
        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('p.content LIKE :search')
               ->setParameter('search', '%' . addcslashes(trim($search), '%_\\') . '%');
        }

        // ── Sort ───────────────────────────────────────────────────────
        $direction = $sort === 'oldest' ? 'ASC' : 'DESC';
        $qb->orderBy('p.createdAt', $direction);

        return $qb->getQuery()->getResult();
    }
}