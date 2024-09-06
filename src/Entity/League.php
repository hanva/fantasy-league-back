<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\LeagueController;
use App\Controller\UserController;
use App\Repository\LeagueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeagueRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/api/league/get-leagues',
            controller: LeagueController::class,
            routeName: 'api_leagues_get_leagues',
            name: 'get leagues',
            description: 'get leagues',
        ),
        new GetCollection(
            uriTemplate: '/api/league/{id}',
            routeName: 'get_leagues_schedule',
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

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'League')]
    private Collection $events;

    #[ORM\Column(type: 'bigint')]
    private ?int $leagueId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setLeague($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getLeague() === $this) {
                $event->setLeague(null);
            }
        }

        return $this;
    }

    public function getLeagueId(): ?int
    {
        return $this->leagueId;
    }

    public function setLeagueId(int $leagueId): static
    {
        $this->leagueId = $leagueId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
