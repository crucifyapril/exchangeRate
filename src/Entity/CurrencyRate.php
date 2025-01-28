<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'currency_rates', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'unique_currency_date', columns: ['currency_code', 'date']),
])]
class CurrencyRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currencyCode;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    private string $rate;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $trend = null;

    public function getTrend(): ?string
    {
        return $this->trend;
    }

    public function setTrend(?string $trend): self
    {
        $this->trend = $trend;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setRate(string $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }
}