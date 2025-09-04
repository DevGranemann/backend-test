<?php

namespace App\Entity;

use App\Repository\InvestmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvestmentRepository::class)]
class Investment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $owner = null;

    #[ORM\Column]
    private ?\DateTime $creationDate = null;

    #[ORM\Column]
    private ?float $investmentValue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): static
    {
        $now = new \DateTime();
        if ($creationDate > $now) {
            throw new \InvalidArgumentException('A data de criação não pode ser futura');
        }

        $this->creationDate = $creationDate;

        return $this;
    }

    public function getInvestmentValue(): ?float
    {
        return $this->investmentValue;
    }

    public function setInvestmentValue(float $investmentValue): static
    {
        if ($investmentValue < 0) {
            throw new \InvalidArgumentException('O valor do investimento não pode ser negativo.');
        }

        $this->investmentValue = $investmentValue;

        return $this;
    }
}
