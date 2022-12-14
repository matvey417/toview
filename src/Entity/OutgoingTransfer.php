<?php

namespace App\Entity;

use App\Repository\OutgoingTransferRepository;
use App\ValueObject\Money;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Сущность исходящего платежа
 */
#[ORM\Entity(repositoryClass: OutgoingTransferRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class OutgoingTransfer
{
    /**
     * ID
     *
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * Платеж
     *
     * @var Payment
     */
    #[ORM\OneToOne(targetEntity: Payment::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Payment $payment;

    /**
     * Пользователь, которому осуществляется перевод
     *
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'outgoingTransfers')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    /**
     * Сумма
     *
     * @var Money
     */
    #[ORM\Column(type: 'money')]
    private Money $sum;

    /**
     * Дата создания
     *
     * @var DateTimeInterface
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeInterface $createdAt;

    /**
     * Комиссия
     */
    #[ORM\OneToMany(mappedBy: 'outgoingTransfer', targetEntity: Commission::class)]
    private $commissions;


    public function __construct()
    {
        $this->commissions = new ArrayCollection();
    }

    /**
     * Возвращает ID
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает платеж
     *
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * Устанавливает платеж
     *
     * @param Payment $payment
     *
     * @return $this
     */
    public function setPayment(Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Возвращает пользователя, которому осуществляется перевод
     *
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * Устанавливает пользователя, которому осуществляется перевод
     *
     * @param User|null $owner
     *
     * @return $this
     */
    public function setOwner(?UserInterface $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Возвращает сумму
     *
     * @return Money|null
     */
    public function getSum(): ?Money
    {
        return $this->sum;
    }

    /**
     * Устанавливает сумму
     *
     * @param Money $sum
     *
     * @return $this
     */
    public function setSum(Money $sum): self
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Возвращает дату создания
     *
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Устанавливает дату создания
     */
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * Возвращает записи комиссий по Outgoing
     */
    public function getCommissions(): Collection
    {
        return $this->commissions;
    }

    public function addCommission(Commission $commission): self
    {
        if (!$this->commissions->contains($commission)) {
            $this->commissions[] = $commission;
            $commission->setOutgoingTransfer($this);
        }

        return $this;
    }

    public function removeCommission(Commission $commission): self
    {
        if ($this->commissions->removeElement($commission)) {
            // set the owning side to null (unless already changed)
            if ($commission->getOutgoingTransfer() === $this) {
                $commission->setOutgoingTransfer(null);
            }
        }

        return $this;
    }
}
