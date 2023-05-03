<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_payment')]
#[ORM\HasLifecycleCallbacks]
class OrderPayment implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'float')]
    private float $amount;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: SellProcess::class, inversedBy: 'orderPayments')]
    #[ORM\JoinColumn(name: 'sell_process_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SellProcess $sellProcess;

    public function __construct(SellProcess $sellProcess)
    {
        $this->amount = $this->getAmount();
        $this->sellProcess = $sellProcess;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->sellProcess->getTotalIncludingTax();
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    public function getSellProcess(): SellProcess
    {
        return $this->sellProcess;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'sellProcess' => $this->sellProcess,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}