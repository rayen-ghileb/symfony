<?php

namespace App\Entity;

use App\Repository\PostMediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostMediaRepository::class)]
#[ORM\Table(name: 'post_media')]
class PostMedia
{
    public const TYPE_IMAGE = 'IMAGE';
    public const TYPE_VIDEO = 'VIDEO';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'mediaList')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    #[ORM\Column(name: 'media_url', length: 500)]
    private string $mediaUrl = '';

    #[ORM\Column(name: 'media_type', length: 10, options: ['default' => 'IMAGE'])]
    private string $mediaType = self::TYPE_IMAGE;

    #[ORM\Column(name: 'display_order', options: ['default' => 0])]
    private int $displayOrder = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    // ── Getters & Setters ──────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): Post
    {
        return $this->post;
    }
    public function setPost(Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getMediaUrl(): string
    {
        return $this->mediaUrl;
    }
    public function setMediaUrl(?string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl ?? '';
        return $this;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }
    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }
    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isImage(): bool
    {
        return $this->mediaType === self::TYPE_IMAGE;
    }
    public function isVideo(): bool
    {
        return $this->mediaType === self::TYPE_VIDEO;
    }

    public static function getTypeChoices(): array
    {
        return [
            'Image' => self::TYPE_IMAGE,
            'Video' => self::TYPE_VIDEO,
        ];
    }
}
