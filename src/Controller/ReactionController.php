<?php

namespace App\Controller;

use App\Entity\Reaction;
use App\Entity\User;
use App\Entity\Post;
use App\Form\ReactionType;
use App\Repository\ReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reactions', name: 'reaction_')]
#[IsGranted('ROLE_USER')]
class ReactionController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_feed');
    }

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ReactionRepository $repo): Response
    {
        $reaction = new Reaction();
        $form = $this->createForm(ReactionType::class, $reaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            // Resolve the Post entity from the hidden field
            $postId = $form->get('post')->getData();
            $post = $em->getRepository(Post::class)->find($postId);
            if (!$post) {
                $this->addFlash('danger', 'Post not found.');
                return $this->redirectToRoute('app_feed');
            }

            // If user already reacted, update the existing reaction
            $existing = $repo->findOneBy(['post' => $post, 'user' => $user]);
            if ($existing !== null) {
                $existing->setReactionType($reaction->getReactionType());
                $em->flush();
                $this->addFlash('success', 'Reaction updated!');
                return $this->redirectToRoute('app_feed');
            }

            $reaction->setPost($post);
            $reaction->setUser($user);

            $em->persist($reaction);
            $em->flush();

            $this->addFlash('success', 'Reaction added!');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/{postId}/{userId}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        int $postId,
        int $userId,
        ReactionRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $post = $em->getRepository(Post::class)->find($postId);
        $user = $em->getRepository(User::class)->find($userId);

        if (!$post || !$user) {
            throw $this->createNotFoundException('Post or User not found.');
        }

        $reaction = $repo->findOneBy(['post' => $post, 'user' => $user]);
        if ($reaction === null) {
            throw $this->createNotFoundException('Reaction not found.');
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $reaction->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$postId.'_'.$userId, $request->request->get('_token'))) {
            $em->remove($reaction);
            $em->flush();
            $this->addFlash('success', 'Reaction removed.');
        }

        return $this->redirectToRoute('app_feed');
    }
}