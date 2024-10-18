<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Contract\Config\AppParameters;
use App\Contract\Config\AppSerializationGroups;
use App\Contract\Doctrine\Entity\DatedEntityInterface;
use App\Contract\Doctrine\Entity\UuidPrimaryKeyInterface;
use App\Doctrine\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[
    ORM\Entity(repositoryClass: UserRepository::class),
    ORM\Table(name: '"user"'),
    ORM\UniqueConstraint(name: 'user_username_uniq', fields: [AppParameters::SECURITY_USER_ENTITY_ID_FIELD]),
]
class User
    implements UuidPrimaryKeyInterface, DatedEntityInterface,
        UserInterface, PasswordAuthenticatedUserInterface
{
    use UuidPrimaryKeyTrait;
    use DatedEntityTrait;

    public const USERNAME_MAX_LENGTH = 180;

    #[
        ORM\Column(name: 'username', type: Types::STRING, length: self::USERNAME_MAX_LENGTH, nullable: false),
        Groups(groups: [AppSerializationGroups::ENTITY_USER]),
        SerializedName(serializedName: 'username'),
    ]
    private ?string $username = null;

    #[
        ORM\Column(name: 'roles', type: Types::JSON, nullable: false),
        Groups(groups: [AppSerializationGroups::ENTITY_USER]),
        SerializedName(serializedName: 'roles'),
    ]
    private array $roles = [];

    #[ORM\Column(name: 'password', type: Types::STRING, nullable: false)]
    private ?string $password = null;

    #[
        ORM\Column(name: 'enabled', type: Types::BOOLEAN, nullable: false),
        Groups(groups: [AppSerializationGroups::ENTITY_USER]),
        SerializedName(serializedName: 'id'),
    ]
    private bool $enabled = false;

    public function __construct()
    {
        $this->generateId();
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has default role
        if (false === in_array(needle: AppParameters::SECURITY_DEFAULT_ROLE, haystack: $roles, strict: true)) {
            $roles[] = AppParameters::SECURITY_DEFAULT_ROLE;
        }

        return $roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
