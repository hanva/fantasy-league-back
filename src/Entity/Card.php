<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CardRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['card:read']]
)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: ['common', 'rare', 'epic', 'player'], message: 'Choose a valid rarity.')]
    private ?string $rarity = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['card:read', 'user:read', 'card:write'])]
    private ?int $basePoints = 0;

    #[ORM\Column(length: 255)]
    #[Groups(['card:read', 'user:read', 'card:write'])]
    private ?string $condition = null;

    #[ORM\OneToMany(targetEntity: Bet::class, mappedBy: "card")]
    private Collection $bets;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBasePoints(): ?int
    {
        return $this->basePoints;
    }

    public function setBasePoints(int $basePoints): static
    {
        $this->basePoints = $basePoints;
        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): static
    {
        $this->condition = $condition;
        return $this;
    }

    public function calculatePoints(array $matchStats): int
    {
        $points = $this->basePoints;

        // Decode the description JSON if it is valid
        $descriptionData = json_decode($this->description, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($descriptionData)) {
            return $points; // If the description is not a valid JSON, return base points
        }

        if (isset($descriptionData['rule'])) {
            switch ($descriptionData['rule']) {
                case 'top_best_kda':
                    if (isset($matchStats['topKDA']) && $matchStats['topBestKDA']) {
                        $points += 20;
                    }
                    break;

                case 'jungle_best_kda':
                    if (isset($matchStats['jungleKDA']) && $matchStats['jungleBestKDA']) {
                        $points += 20;
                    }
                    break;

                case 'mid_best_kda':
                    if (isset($matchStats['midKDA']) && $matchStats['midBestKDA']) {
                        $points += 20;
                    }
                    break;

                case 'adc_best_kda':
                    if (isset($matchStats['adcKDA']) && $matchStats['adcBestKDA']) {
                        $points += 20;
                    }
                    break;

                case 'support_best_kda':
                    if (isset($matchStats['supportKDA']) && $matchStats['supportBestKDA']) {
                        $points += 20;
                    }
                    break;

                case 'top_kills_assists':
                    if (isset($matchStats['topKills'], $matchStats['topAssists'])) {
                        $points += ($matchStats['topKills'] + $matchStats['topAssists']) * 20;
                    }
                    break;

                case 'top_kill_or_assist':
                    if (isset($matchStats['topKills'], $matchStats['topAssists'])) {
                        $points += ($matchStats['topKills'] + $matchStats['topAssists']) * $descriptionData['points_per_kill_assist'];
                    }
                    break;

            }
        }

        return $points;
    }
}
