<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\LeagueController;
use App\Controller\UserController;
use App\Repository\LeagueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeagueRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/users/register',
            controller: LeagueController::class,
            routeName: 'api_league_get_next_matches_from_league',
            name: 'get next matches from league',
            description: 'get Matches from League',
        ),
    ]
)]
class League
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
