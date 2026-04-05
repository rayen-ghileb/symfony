<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'posts')]
#[ORM\HasLifecycleCallbacks]
class Post
{
    public const VISIBILITY_PUBLIC = 'PUBLIC';
    public const VISIBILITY_PATIENTS_ONLY = 'PATIENTS_ONLY';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
    private User $author;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(name: 'media_url', length: 255, nullable: true)]
    private ?string $mediaUrl = null;

    #[ORM\Column(length: 20, enumType: null, options: ['default' => 'PATIENTS_ONLY'])]
    private string $visibility = self::VISIBILITY_PATIENTS_ONLY;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    #[ORM\Column(name: 'is_deleted', options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(name: 'deleted_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: PostMedia::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['displayOrder' => 'ASC'])]
    private Collection $mediaList;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Reaction::class, cascade: ['persist', 'remove'])]
    private Collection $reactions;

    public function __construct()
    {
        $this->mediaList = new ArrayCollection();
        $this->comments  = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // ── Getters & Setters ──────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }
    public function setAuthor(User $author): static
    {
        $this->author = $author;
        return $this;
    }
    public function getAuthorName(): string
    {
        return $this->author->getFullName();
    }


    public function getContent(): string
    {
        return $this->content;
    }
    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }
    public function setMediaUrl(?string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;
        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }
    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }
    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getMediaList(): Collection
    {
        return $this->mediaList;
    }
    public function setMediaList(iterable $mediaList): static
    {
        $newMediaList = [];
        foreach ($mediaList as $media) {
            $newMediaList[] = $media;
        }

        $this->mediaList->clear();
        foreach ($newMediaList as $media) {
            $this->addMedia($media);
        }

        return $this;
    }
    public function addMedia(PostMedia $media): static
    {
        if (!$this->mediaList->contains($media)) {
            $this->mediaList->add($media);
            $media->setPost($this);
        }
        return $this;
    }
    public function removeMedia(PostMedia $media): static
    {
        $this->mediaList->removeElement($media);
        return $this;
    }
    public function hasMedia(): bool
    {
        return !$this->mediaList->isEmpty();
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function getReactionCounts(): array
    {
        $counts = [];
        foreach ($this->reactions as $reaction) {
            $type = $reaction->getReactionType();
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }
        return $counts;
    }

    public function getTotalReactions(): int
    {
        return $this->reactions->count();
    }

    public static function getVisibilityChoices(): array
    {
        return [
            'Public'        => self::VISIBILITY_PUBLIC,
            'Patients Only' => self::VISIBILITY_PATIENTS_ONLY,
        ];
    }
    public function getUserReaction(?User $user): ?string
    {
        if (!$user) return null;
        foreach ($this->reactions as $reaction) {
            if ($reaction->getUser()->getId() === $user->getId()) {
                return $reaction->getReactionType();
            }
        }
        return null;
    }
}
