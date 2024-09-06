<?php

namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use App\Controller\UserController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use App\Trait\Serializable;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(
            openapiContext: [
                'security' => [['JWT' => []]],
            ],
        ),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new GetCollection(
            uriTemplate: '/api/users/bets/list',
            routeName: 'api_users_get_bets',
            controller: UserController::class,
            openapiContext: [
                'security' => [['JWT' => []]],
                'summary' => 'Get bets owned by the current user',
                'description' => 'Returns a collection of bets owned by the authenticated user.',
            ],
            normalizationContext: ['groups' => ['user:get_bets']],
        ),
        new Post(
            uriTemplate: '/api/users/by-email',
            routeName: 'api_users_get_by_email',
            controller: UserController::class,
            openapiContext: [
                'summary' => 'Get a user by email',
                'description' => 'Because API Only gets by ID by default',
                'security' => [['JWT' => []]],
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'example' => 'root@root.fr',
                                    ],
                                ],
                                'required' => ['email'],
                            ],
                        ],
                    ],
                ],
            ],
            security: "is_granted('ROLE_USER')",
            name: 'email'),
        new Post(
            uriTemplate: '/api/users/register',
            routeName: 'api_users_register',
            controller: UserController::class,
            openapiContext: [
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'example' => 'root@root.fr',
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                        'example' => 'root',
                                    ],
                                ],
                                'required' => ['email', 'password'],
                            ],
                        ],
                    ],
                ],
                'security' => [],
                'summary' => 'Creates an user',
            ],
            name: 'register'
        ),
    ],
    normalizationContext: ['groups' => ['user:read', 'user:get_bets']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Serializable;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'string')]
    private $password;

    /**
     * @var Collection<int, Bet>
     */
    #[ORM\OneToMany(targetEntity: Bet::class, mappedBy: 'user')]
    private Collection $bets;

    public function __construct()
    {
        $this->bets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données sensibles sur l'utilisateur, effacez-les ici
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[Groups(['user:read', 'user:get_bets', 'user:write'])]
    public function getBets(): Collection
    {
        return $this->bets;
    }

    public function addBet(Bet $bet): static
    {
        if (!$this->bets->contains($bet)) {
            $this->bets->add($bet);
            $bet->setUser($this);
        }

        return $this;
    }

    public function removeBet(Bet $bet): static
    {
        if ($this->bets->removeElement($bet)) {
            // set the owning side to null (unless already changed)
            if ($bet->getUser() === $this) {
                $bet->setUser(null);
            }
        }

        return $this;
    }
}