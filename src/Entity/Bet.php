<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\LeagueController;
use App\Repository\BetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BetRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['bet:read', 'user:read']],
    denormalizationContext: ['groups' => ['bet:write']]
)]
class Bet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bet:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint')]
    #[Groups(['bet:read', 'bet:write', 'user:read'])]
    private ?int $leagueEventId = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['bet:read', 'bet:write'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['bet:read', 'bet:write', 'user:read'])]
    private ?int $team = null;

    #[ORM\Column]
    #[Groups(['bet:read', 'bet:write', 'user:read'])]
    private ?int $gameWins = null;

    #[ORM\Column]
    #[Groups(['bet:read', 'bet:write', 'user:read'])]
    private ?int $gameLooses = null;

    #[ORM\Column]
    private ?int $score = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLeagueEventId(): ?int
    {
        return $this->leagueEventId;
    }

    public function setLeagueEventId(int $leagueEventId): static
    {
        $this->leagueEventId = $leagueEventId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTeam(): ?int
    {
        return $this->team;
    }

    public function setTeam(int $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getGameWins(): ?int
    {
        return $this->gameWins;
    }

    public function setGameWins(int $gameWins): static
    {
        $this->gameWins = $gameWins;

        return $this;
    }

    public function getGameLooses(): ?int
    {
        return $this->gameLooses;
    }

    public function setGameLooses(int $gameLooses): static
    {
        $this->gameLooses = $gameLooses;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }
}
