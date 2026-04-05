<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_PATIENT = 'PATIENT';
    public const ROLE_PSYCHIATRIST = 'PSYCHIATRIST';
    public const ROLE_ADMIN = 'ADMIN';

    public const AUTH_PROVIDER_LOCAL = 'LOCAL';
    public const AUTH_PROVIDER_GOOGLE = 'GOOGLE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "full_name", type: "string", length: 100)]
    private string $fullName;

    #[ORM\Column(type: "string", length: 100, unique: true)]
    private string $email;

    #[ORM\Column(name: "password", type: "string", length: 255)]
    private string $password;

    #[ORM\Column(name: "phone_number", type: "string", length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(name: "role", type: "string", length: 20, options: ["default" => "PATIENT"])]
    private string $role = self::ROLE_PATIENT;

    #[ORM\Column(name: "is_active", type: "boolean", options: ["default" => true])]
    private bool $isActive = true;

    #[ORM\Column(name: "created_at", type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: "profile_picture", type: "string", length: 500, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column(name: "is_approved", type: "boolean", options: ["default" => true])]
    private bool $isApproved = true;

    #[ORM\Column(name: "gender", type: "string", length: 20, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(name: "date_of_birth", type: "date", nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(name: "emergency_contact", type: "string", length: 100, nullable: true)]
    private ?string $emergencyContact = null;

    #[ORM\Column(name: "specialization", type: "string", length: 100, nullable: true)]
    private ?string $specialization = null;

    #[ORM\Column(name: "license_number", type: "string", length: 50, nullable: true)]
    private ?string $licenseNumber = null;

    #[ORM\Column(name: "google_id", type: "string", length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(name: "auth_provider", type: "string", length: 20, options: ["default" => "LOCAL"])]
    private string $authProvider = self::AUTH_PROVIDER_LOCAL;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class, cascade: ['remove'])]
private Collection $posts;

#[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class, cascade: ['remove'])]
private Collection $comments;

#[ORM\OneToMany(mappedBy: 'user', targetEntity: Reaction::class, cascade: ['remove'])]
private Collection $reactions;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        if ($this->role === self::ROLE_ADMIN) {
            $roles[] = 'ROLE_ADMIN';
        } elseif ($this->role === self::ROLE_PSYCHIATRIST) {
            $roles[] = 'ROLE_PSYCHIATRIST';
        } else {
            $roles[] = 'ROLE_PATIENT';
        }

        return array_values(array_unique($roles));
    }

    public function eraseCredentials(): void
    {
    }

    public function getId(): ?int { return $this->id; }

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $fullName): self {
        $this->fullName = $fullName; return $this;
    }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self {
        $this->email = $email; return $this;
    }

    public function setPassword(string $password): self {
        $this->password = $password; return $this;
    }

    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(?string $phone): self {
        $this->phoneNumber = $phone; return $this;
    }

    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): self {
        $this->address = $address; return $this;
    }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): self {
        $role = strtoupper($role);
        if (!in_array($role, [self::ROLE_PATIENT, self::ROLE_PSYCHIATRIST, self::ROLE_ADMIN], true)) {
            $role = self::ROLE_PATIENT;
        }
        $this->role = $role; return $this;
    }

    // Compatibility with existing usage.
    public function getUserType(): string { return $this->getRole(); }
    public function setUserType(string $type): self { return $this->setRole($type); }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): self {
        $this->isActive = $isActive; return $this;
    }

    public function isApproved(): bool { return $this->isApproved; }
    public function setIsApproved(bool $isApproved): self {
        $this->isApproved = $isApproved; return $this;
    }

    public function getProfilePicture(): ?string { return $this->profilePicture; }
    public function setProfilePicture(?string $profilePicture): self {
        $this->profilePicture = $profilePicture; return $this;
    }

    public function getGender(): ?string { return $this->gender; }
    public function setGender(?string $gender): self {
        $this->gender = $gender; return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface { return $this->dateOfBirth; }
    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self {
        $this->dateOfBirth = $dateOfBirth; return $this;
    }

    public function getEmergencyContact(): ?string { return $this->emergencyContact; }
    public function setEmergencyContact(?string $emergencyContact): self {
        $this->emergencyContact = $emergencyContact; return $this;
    }

    public function getSpecialization(): ?string { return $this->specialization; }
    public function setSpecialization(?string $specialization): self {
        $this->specialization = $specialization; return $this;
    }

    public function getLicenseNumber(): ?string { return $this->licenseNumber; }
    public function setLicenseNumber(?string $licenseNumber): self {
        $this->licenseNumber = $licenseNumber; return $this;
    }

    public function getGoogleId(): ?string { return $this->googleId; }
    public function setGoogleId(?string $googleId): self {
        $this->googleId = $googleId; return $this;
    }

    public function getAuthProvider(): string { return $this->authProvider; }
    public function setAuthProvider(string $authProvider): self {
        $authProvider = strtoupper($authProvider);
        if (!in_array($authProvider, [self::AUTH_PROVIDER_LOCAL, self::AUTH_PROVIDER_GOOGLE], true)) {
            $authProvider = self::AUTH_PROVIDER_LOCAL;
        }
        $this->authProvider = $authProvider; return $this;
    }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function getPosts(): Collection { return $this->posts; }
public function getComments(): Collection { return $this->comments; }
public function getReactions(): Collection { return $this->reactions; }

public function getInitials(): string
{
    $parts = explode(' ', $this->fullName);
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($this->fullName, 0, 1));
}
}