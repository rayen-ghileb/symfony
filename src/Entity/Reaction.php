<?php

namespace App\Entity;

use App\Repository\ReactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReactionRepository::class)]
#[ORM\Table(name: 'reactions')]
class Reaction
{
    public const TYPE_LIKE    = 'LIKE';
    public const TYPE_LOVE    = 'LOVE';
    public const TYPE_SUPPORT = 'SUPPORT';

    private const EMOJIS = [
        self::TYPE_LIKE    => '👍',
        self::TYPE_LOVE    => '❤️',
        self::TYPE_SUPPORT => '💙',
    ];

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'reactions')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reactions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'reaction_type', length: 20)]
    private string $reactionType;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // ── Getters & Setters ──────────────────────────────────────────────────

    public function getPost(): Post
    {
        return $this->post;
    }
    public function setPost(Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getReactionType(): string
    {
        return $this->reactionType;
    }
    public function setReactionType(string $reactionType): static
    {
        $this->reactionType = $reactionType;
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

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getEmoji(): string
    {
        return self::EMOJIS[$this->reactionType] ?? '👍';
    }

    public static function getEmojis(): array
    {
        return self::EMOJIS;
    }

    public static function getTypeChoices(): array
    {
        $choices = [];
        foreach (self::EMOJIS as $type => $emoji) {
            $label = $emoji . ' ' . ucfirst(strtolower(str_replace('_', ' ', $type)));
            $choices[$label] = $type;
        }
        return $choices;
    }

    public static function getAllTypes(): array
    {
        return array_keys(self::EMOJIS);
    }
}