<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FeedController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_feed');
    }

    #[Route('/feed', name: 'app_feed', methods: ['GET'])]
    public function feed(Request $request, PostRepository $postRepo): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $filter = $request->query->get('filter');   // 'PUBLIC' | 'PATIENTS_ONLY' | null
        $search = $request->query->get('search');   // keyword string | null
        $sort   = $request->query->get('sort', 'newest'); // 'newest' | 'oldest'

        // Sanitise sort to only two accepted values
        if (!in_array($sort, ['newest', 'oldest'], true)) {
            $sort = 'newest';
        }

        $posts = $postRepo->findForFeed($currentUser, $filter, $search, $sort);

        // ── AJAX request: return rendered post-list partial only ────────
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            $html = $this->renderView('feed/_posts.html.twig', [
                'posts'       => $posts,
                'currentUser' => $currentUser,
            ]);
            return new JsonResponse(['html' => $html]);
        }

        // ── Full page load ─────────────────────────────────────────────
        return $this->render('feed/index.html.twig', [
            'posts'       => $posts,
            'currentUser' => $currentUser,
            'filter'      => $filter,
            'search'      => $search,
            'sort'        => $sort,
        ]);
    }
}