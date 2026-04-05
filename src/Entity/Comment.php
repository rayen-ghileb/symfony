<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comments')]
#[ORM\HasLifecycleCallbacks]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Post $post;

#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
#[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
private User $author;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    // Self-referencing: parent comment (null = top-level comment)
#[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: 'replies')]
#[ORM\JoinColumn(name: 'parent_comment_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
private ?Comment $parentComment = null;

#[ORM\OneToMany(mappedBy: 'parentComment', targetEntity: Comment::class, cascade: ['remove'])]
#[ORM\OrderBy(['createdAt' => 'ASC'])]
private Collection $replies;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    #[ORM\Column(name: 'is_edited', options: ['default' => false])]
    private bool $edited = false;

    #[ORM\Column(name: 'is_deleted', options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(name: 'deleted_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->replies   = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
    

    // ── Getters & Setters ──────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getPost(): Post { return $this->post; }
    public function setPost(Post $post): static { $this->post = $post; return $this; }

    public function getAuthor(): User { return $this->author; }
    public function setAuthor(User $author): static { $this->author = $author; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getParentComment(): ?Comment { return $this->parentComment; }
    public function setParentComment(?Comment $parentComment): static { $this->parentComment = $parentComment; return $this; }

    public function getReplies(): Collection { return $this->replies; }
    public function addReply(Comment $reply): static
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setParentComment($this);
        }
        return $this;
    }
    public function removeReply(Comment $reply): static
    {
        if ($this->replies->removeElement($reply)) {
            if ($reply->getParentComment() === $this) {
                $reply->setParentComment(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function isEdited(): bool { return $this->edited; }
    public function setEdited(bool $edited): static { $this->edited = $edited; return $this; }

    public function isDeleted(): bool { return $this->deleted; }
    public function setDeleted(bool $deleted): static { $this->deleted = $deleted; return $this; }

    public function getDeletedAt(): ?\DateTimeInterface { return $this->deletedAt; }
    public function setDeletedAt(?\DateTimeInterface $deletedAt): static { $this->deletedAt = $deletedAt; return $this; }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isTopLevel(): bool { return $this->parentComment === null; }
    public function isReply(): bool    { return $this->parentComment !== null; }
    public function hasReplies(): bool { return !$this->replies->isEmpty(); }
    public function getReplyCount(): int { return $this->replies->count(); }

    public function softDelete(): void
    {
        $this->deleted   = true;
        $this->deletedAt = new \DateTime();
    }
    
}