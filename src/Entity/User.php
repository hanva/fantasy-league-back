<?php

namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use App\Controller\UserController;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use App\Trait\Serializable;


#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(
            uriTemplate: '/user/by-email',
            routeName: 'api_user_get_by_email',
            controller: UserController::class,
            openapiContext: [
                'summary' => 'Get a user by email',
                'description' => 'Because API Only gets by ID by default',
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
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
//        new Put(),
//        new Delete(),
        new Post(
            uriTemplate: '/user/register',
            controller: UserController::class,
            routeName: 'api_user_register',
            name: 'register',
            openapiContext: [
                'summary' => 'Creates an user',
            ],
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Serializable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'string')]
    private $password;

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
}