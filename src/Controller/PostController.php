<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostMedia;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/posts', name: 'post_')]
#[IsGranted('ROLE_USER')]
class PostController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PostRepository $repo): Response
    {
        return $this->redirectToRoute('app_feed');
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('GET')) {
            return $this->redirectToRoute('app_feed');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user instanceof User) {
                // FIXED: Use setAuthor instead of setAuthorId
                $post->setAuthor($user);
                $post->setCreatedAt(new \DateTime());
                $post->setUpdatedAt(new \DateTime());

                try {
                    $this->processMediaUploads($post, $form);
                } catch (\RuntimeException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_feed');
                }

                $em->persist($post);
                $em->flush();

                $this->addFlash('success', 'Post created successfully!');
                return $this->redirectToRoute('app_feed');
            }
            throw $this->createAccessDeniedException();
        }

        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('danger', $error->getMessage());
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->redirectToRoute('app_feed');
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        // Check if user owns this post
        $user = $this->getUser();
        if (!$user instanceof User || $post->getAuthor()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You can only edit your own posts');
        }

        if ($request->isMethod('GET')) {
            return $this->redirectToRoute('app_feed');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTime());

            try {
                $this->processMediaUploads($post, $form);
            } catch (\RuntimeException $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('app_feed');
            }

            $em->flush();
            $this->addFlash('success', 'Post updated successfully!');
            return $this->redirectToRoute('app_feed');
        }

        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('danger', $error->getMessage());
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        // Check if user owns this post
        $user = $this->getUser();
        if (!$user instanceof User || $post->getAuthor()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You can only delete your own posts');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            // Soft delete instead of hard delete
            $post->setDeleted(true);
            $post->setDeletedAt(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'Post deleted.');
        }

        return $this->redirectToRoute('app_feed');
    }

    private function processMediaUploads(Post $post, FormInterface $form): void
    {
        if ($form->has('mediaList')) {
            $mediaListForm = $form->get('mediaList');
            $toRemove = [];

            foreach ($post->getMediaList() as $index => $media) {
                $media->setPost($post);

                $mediaForm = $mediaListForm->has((string) $index)
                    ? $mediaListForm->get((string) $index)
                    : null;

                $uploadedFile = null;
                if ($mediaForm !== null && $mediaForm->has('mediaFile')) {
                    $candidate = $mediaForm->get('mediaFile')->getData();
                    if ($candidate instanceof UploadedFile) {
                        $uploadedFile = $candidate;
                    }
                }

                if ($uploadedFile instanceof UploadedFile) {
                    [$storedPath, $mediaType] = $this->storeUploadedMedia($uploadedFile);
                    $media->setMediaUrl($storedPath);
                    $media->setMediaType($mediaType);
                    if ($media->getCreatedAt() === null) {
                        $media->setCreatedAt(new \DateTime());
                    }
                    continue;
                }

                if ('' === trim($media->getMediaUrl())) {
                    $toRemove[] = $media;
                }
            }

            foreach ($toRemove as $mediaToRemove) {
                $post->removeMedia($mediaToRemove);
            }
        }

        $this->appendUploadedMediaFiles($post, $form);
    }

    private function appendUploadedMediaFiles(Post $post, FormInterface $form): void
    {
        if (!$form->has('mediaFiles')) {
            return;
        }

        $uploadedFiles = $form->get('mediaFiles')->getData();
        if (!is_iterable($uploadedFiles)) {
            return;
        }

        $nextDisplayOrder = 0;
        foreach ($post->getMediaList() as $existingMedia) {
            $nextDisplayOrder = max($nextDisplayOrder, $existingMedia->getDisplayOrder() + 1);
        }

        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile instanceof UploadedFile) {
                continue;
            }

            [$storedPath, $mediaType] = $this->storeUploadedMedia($uploadedFile);

            $media = new PostMedia();
            $media->setPost($post);
            $media->setMediaUrl($storedPath);
            $media->setMediaType($mediaType);
            $media->setDisplayOrder($nextDisplayOrder++);
            $media->setCreatedAt(new \DateTime());

            $post->addMedia($media);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storeUploadedMedia(UploadedFile $uploadedFile): array
    {
        $mimeType = $uploadedFile->getMimeType() ?? '';
        if (str_starts_with($mimeType, 'image/')) {
            $mediaType = PostMedia::TYPE_IMAGE;
        } elseif (str_starts_with($mimeType, 'video/')) {
            $mediaType = PostMedia::TYPE_VIDEO;
        } else {
            throw new \RuntimeException('Only image and video files are allowed.');
        }

        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $uploadDir = $projectDir . '/public/uploads/post_media';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Unable to prepare media upload directory.');
        }

        $extension = $uploadedFile->guessExtension();
        if (!$extension) {
            $extension = str_starts_with($mimeType, 'video/') ? 'mp4' : 'jpg';
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadedFile->move($uploadDir, $fileName);

        return ['/uploads/post_media/' . $fileName, $mediaType];
    }
}