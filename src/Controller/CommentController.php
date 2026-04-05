<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Post;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            // Resolve the Post entity from the hidden field value
            $postId = $form->get('post')->getData();
            $post = $em->getRepository(Post::class)->find($postId);
            if (!$post) {
                $this->addFlash('danger', 'Post not found.');
                return $this->redirectToRoute('app_feed');
            }

            // Resolve optional parent comment
            $parentId = $form->get('parentComment')->getData();
            if ($parentId) {
                $parent = $em->getRepository(Comment::class)->find($parentId);
                if ($parent) {
                    $comment->setParentComment($parent);
                }
            }

            $comment->setPost($post);
            $comment->setAuthor($user);

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Comment added!');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User || $comment->getAuthor()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You can only edit your own comments.');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Re-resolve post (the hidden field is unmapped but still submitted)
            $postId = $form->get('post')->getData();
            if ($postId) {
                $post = $em->getRepository(Post::class)->find($postId);
                if ($post) {
                    $comment->setPost($post);
                }
            }

            $comment->setEdited(true);
            $em->flush();

            $this->addFlash('success', 'Comment updated!');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
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

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $comment->softDelete();
            $em->flush();
            $this->addFlash('success', 'Comment removed.');
        }

        return $this->redirectToRoute('app_feed');
    }
}