<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\LeagueController;
use App\Repository\BetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BetRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(),
        new Put(
            openapiContext: [
                'security' => [['JWT' => []]],
            ],
        ),
        new Post(
            openapiContext: [
                'security' => [['JWT' => []]],
            ],
        ),
    ],
    normalizationContext: ['groups' => ['bet:read', 'user:read']],
    denormalizationContext: ['groups' => ['bet:write']]
)]
class Bet
{
    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(targetEntity: Bet::class, mappedBy: 'user')]
    private Collection $bets;
    #[ORM\ManyToMany(targetEntity: Card::class)]
    #[ORM\JoinTable(name: "bet_card")]
    #[Groups(['bet:read', 'bet:write'])]
    private Collection $cards;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bet:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['bet:read', 'bet:write', 'user:read'])]
    private ?string $leagueEventId = null;

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

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        $this->cards->removeElement($card);
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLeagueEventId(): ?string
    {
        return $this->leagueEventId;
    }

    public function setLeagueEventId(string $leagueEventId): static
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
