<?php

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column()]
    private string $baseCurrency;

    #[ORM\Column()]
    private string $targetCurrency;

    #[ORM\Column(type: Types::FLOAT)]
    private float $rate;

    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        columnDefinition: "DEFAULT CURRENT_TIMESTAMP"
    )]
    private DateTimeInterface $createdAt;

    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        columnDefinition: "ON UPDATE CURRENT_TIMESTAMP"
    )]
    private DateTimeInterface $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(string $baseCurrency): self
    {
        $this->baseCurrency = $baseCurrency;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetCurrency(): string
    {
        return $this->targetCurrency;
    }

    /**
     * @param string $targetCurrency
     * @return ExchangeRate
     */
    public function setTargetCurrency(string $targetCurrency): ExchangeRate
    {
        $this->targetCurrency = $targetCurrency;
        return $this;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     * @return ExchangeRate
     */
    public function setRate(float $rate): ExchangeRate
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return ExchangeRate
     */
    #[ORM\PrePersist]
    public function setCreatedAt(): ExchangeRate
    {
        $this->createdAt = new DateTime();
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return ExchangeRate
     */
    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function setUpdatedAt(): ExchangeRate
    {
        $this->updatedAt = new DateTime();
        return $this;
    }

}
