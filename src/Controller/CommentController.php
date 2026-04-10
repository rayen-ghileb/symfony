<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Post;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comments', name: 'comment_')]
#[IsGranted('ROLE_USER')]
class CommentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_feed');
    }

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            // Resolve post
            $postId = $form->get('post')->getData();
            $post   = $em->find(Post::class, (int)$postId);
            if (!$post || $post->isDeleted()) {
                if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                    return new JsonResponse(['success' => false, 'error' => 'Post not found.'], 404);
                }
                $this->addFlash('danger', 'Post not found.');
                return $this->redirectToRoute('app_feed');
            }

            // Resolve optional parent comment
            $parentId = $form->get('parentComment')->getData();
            if ($parentId) {
                $parent = $em->find(Comment::class, (int)$parentId);
                if ($parent && !$parent->isDeleted()) {
                    $comment->setParentComment($parent);
                }
            }

            $comment->setPost($post);
            $comment->setAuthor($user);
            $comment->setCreatedAt(new \DateTime());
            $comment->setUpdatedAt(new \DateTime());

            $em->persist($comment);
            $em->flush();

            if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse(['success' => true]);
            }

            return $this->redirectToRoute('app_feed');
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        foreach ($errors as $error) {
            $this->addFlash('danger', $error);
        }
        return $this->redirectToRoute('app_feed');
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($comment->getAuthor()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setEdited(true);
            $comment->setUpdatedAt(new \DateTime());
            $em->flush();

            if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => true,
                    'content' => $comment->getContent(),
                ]);
            }

            return $this->redirectToRoute('app_feed');
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        foreach ($errors as $error) {
            $this->addFlash('danger', $error);
        }
        return $this->redirectToRoute('app_feed');
    }


    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User || $comment->getAuthor()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You can only delete your own comments.');
        }

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $comment->softDelete();
            $em->flush();
            $this->addFlash('success', 'Comment removed.');
        }

        return $this->redirectToRoute('app_feed');
    }
}
