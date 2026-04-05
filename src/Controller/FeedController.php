<?php

namespace App\Controller;

use App\Entity\Reaction;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FeedController extends AbstractController
{
    #[Route('/feed', name: 'app_feed', methods: ['GET'])]
    public function feed(PostRepository $postRepository): Response
    {
        $posts = $postRepository->createQueryBuilder('p')
            ->leftJoin('p.author',   'a')->addSelect('a')
            ->leftJoin('p.mediaList','m')->addSelect('m')
            ->leftJoin('p.reactions','r')->addSelect('r')
            ->leftJoin('r.user',     'ru')->addSelect('ru')
            ->leftJoin('p.comments', 'c',  'WITH', 'c.parentComment IS NULL AND c.deleted = false')
            ->addSelect('c')
            ->leftJoin('c.author',   'ca')->addSelect('ca')
            ->leftJoin('c.replies',  'rep','WITH', 'rep.deleted = false')
            ->addSelect('rep')
            ->leftJoin('rep.author', 'repa')->addSelect('repa')
            ->where('p.deleted = false')
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('c.createdAt', 'ASC')
            ->addOrderBy('rep.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('feed/index.html.twig', [
            'posts'          => $posts,
            'currentUser'    => $this->getUser(),
            'reactionEmojis' => Reaction::getEmojis(), // now returns only 3
        ]);
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_feed');
        }
        return $this->redirectToRoute('app_login');
    }
}