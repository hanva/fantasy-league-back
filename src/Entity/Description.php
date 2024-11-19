<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DescriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DescriptionRepository::class)]
#[ApiResource]
class Description
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
