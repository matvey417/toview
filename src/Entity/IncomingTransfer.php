<?php

namespace App\Entity;

use App\Repository\IncomingTransferRepository;
use App\ValueObject\Money;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Сущность входящего платежа
 */
#[ORM\Entity(repositoryClass: IncomingTransferRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class IncomingTransfer
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
    #[ORM\OneToOne(inversedBy: 'incomingTransfer', targetEntity: Payment::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $payment;

    /**
     * Пользователь внесший оплату
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * Проект по которому задонатили деньги
     *
     * @var  project
     */
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'incomings')]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    /**
     * Сумма
     *
     * @var Money
     */
    #[ORM\Column(type: 'money')]
    private Money $sum;

    /**
     * Комиссия
     *
     * @var Commission|null
     */
    #[ORM\OneToOne(inversedBy: 'incomingTransfer', targetEntity: Commission::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private $commission;

    /**
     * Дата создания
     *
     * @var DateTimeInterface
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeInterface $createdAt;

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
     * Возвращает пользователя
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Устанавливает пользователя
     *
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Возвращает проект
     *
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * Устанавливает проект
     *
     * @param Project|null $project
     *
     * @return $this
     */
    public function setProject(?Project $project): self
    {
        $this->project = $project;

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
     * Возвращает комиссию
     *
     * @return Commission|null
     */
    public function getCommission(): ?Commission
    {
        return $this->commission;
    }

    /**
     * Устанавливает комиссию
     *
     * @param Commission|null $commission
     *
     * @return IncomingTransfer
     */
    public function setCommission(?Commission $commission): self
    {
        $this->commission = $commission;

        return $this;
    }
}
