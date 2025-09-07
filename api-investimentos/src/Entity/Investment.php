<?php

namespace App\Entity;

use App\Repository\InvestmentRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Owner;

#[ORM\Entity(repositoryClass: InvestmentRepository::class)]
class Investment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $withdrawnGain = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $creationDate = null;

    #[ORM\Column]
    private ?float $investmentValue = null;

    #[ORM\ManyToOne(targetEntity: Owner::class, inversedBy: 'investments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Owner $owner = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $withdrawnAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?Owner
    {
        return $this->owner;
    }

    public function setOwner(Owner $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): self
    {
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

    public function getWithdrawnAt(): ?\DateTime
    {
        return $this->withdrawnAt;
    }

    public function setWithdrawnAt(?\DateTime $date): static
    {
        $this->withdrawnAt = $date;

        return $this;
    }

    public function getWithdrawnGain(): ?float
    {
        return $this->withdrawnGain;
    }

    public function setWithdrawnGain(?float $withdrawnGain): self
    {
        $this->withdrawnGain = $withdrawnGain;
        return $this;
    }
}
