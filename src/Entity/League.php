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
            uriTemplate: '/api/leagues/get-leagues',
            controller: LeagueController::class,
            routeName: 'api_league_get_leagues',
            name: 'get leagues',
            description: 'get leagues',
        ),
        new GetCollection(
            uriTemplate: '/api/league/{id}',
            routeName: 'get_league_schedule',
            defaults: ['id' => '98767991302996019'],
            controller: LeagueController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'onlyNextMatches',
                        'in' => 'query',
                        'description' => 'Filter to get only the next matches',
                        'required' => false,
                        'schema' => [
                            'type' => 'boolean'
                        ]
                    ]
                ],
                'summary' => 'Get Collections of Matches',
                'description' => 'Can be both full schedule or next matches, depending on the query parameter',
            ],
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
