<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use App\ValueObject\Money;
use App\ValueObject\PaymentDestinationType;
use App\ValueObject\PaymentStatus;
use App\ValueObject\RecurrentStatus;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Сущность платежа
 */
#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Payment
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
     * ID карты
     *
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $cardId;

    /**
     * Пользователь
     *
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * Проект
     *
     * @var Project|null
     */
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Project $project;

    /**
     * Email
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $email;

    /**
     * OrderId
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $orderId;

    /**
     * Сумма платежа
     *
     * @var Money
     */
    #[ORM\Column(type: 'money')]
    private Money $sum;

    /**
     * Статус платежа
     *
     * @var PaymentStatus
     */
    #[ORM\Column(type: 'paymentStatus')]
    private PaymentStatus $status;

    /**
     * Платежная система
     *
     * @var Merchant
     */
    #[ORM\ManyToOne(targetEntity: Merchant::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private Merchant $merch;

    /**
     * ID транзакции
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $systemId;

    /**
     * Колбэк
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private string $callback;

    /**
     * Назначение платежа
     *
     * @var PaymentDestinationType
     */
    #[ORM\Column(type: 'paymentDestinationType')]
    private PaymentDestinationType $destination;

    /**
     * Является ли платеж рекуррентным
     *
     * @var RecurrentStatus
     */
    #[ORM\Column(type: 'recurrentStatus')]
    private RecurrentStatus $recurrent;

    /**
     * Дата создания
     *
     * @var DateTimeInterface
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeInterface $createdAt;

    /**
     * Дата обновления
     *
     * @var DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeInterface $updatedAt;

    /**
     * Дата удаления
     *
     * @var DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeInterface $deletedAt;

    /**
     * Подписка
     *
     * @var Subscription|null
     */
    #[ORM\OneToOne(mappedBy: 'payment', targetEntity: Subscription::class, cascade: ['persist', 'remove'])]
    private ?Subscription $subscription;

    /**
     * Чек
     *
     * @var Check|null
     */
    #[ORM\OneToOne(mappedBy: 'payment', targetEntity: Check::class, cascade: ['persist', 'remove'])]
    private ?Check $userCheck;

    #[ORM\OneToOne(mappedBy: 'payment', targetEntity: IncomingTransfer::class, cascade: ['persist', 'remove'])]
    private $incomingTransfer;

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
     * Возвращает пользователя
     *
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Устанавливает пользователя
     *
     * @param UserInterface|null $user
     *
     * @return $this
     */
    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Возвращает Email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Устанавливает Email
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Возвращает OrderId
     *
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * Устанавливает OrderId
     *
     * @param string $orderId
     *
     * @return $this
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

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
     * Возвращает статус
     *
     * @return PaymentStatus
     */
    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    /**
     * Устанавливает статус
     *
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Возвращает платежную систему
     *
     * @return Merchant|null
     */
    public function getMerch(): ?Merchant
    {
        return $this->merch;
    }

    /**
     * Устанавливает платежную систему
     *
     * @param Merchant|null $merch
     *
     * @return $this
     */
    public function setMerch(?Merchant $merch): self
    {
        $this->merch = $merch;

        return $this;
    }

    /**
     * Возвращает ID транзакции
     *
     * @return string|null
     */
    public function getSystemId(): ?string
    {
        return $this->systemId;
    }

    /**
     * Устанавливает ID транзакции
     *
     * @param string|null $systemId
     *
     * @return $this
     */
    public function setSystemId(?string $systemId): self
    {
        $this->systemId = $systemId;

        return $this;
    }

    /**
     * Возвращает колбэк
     *
     * @return string|null
     */
    public function getCallback(): ?string
    {
        return $this->callback;
    }

    /**
     * Устанавливает колбэк
     *
     * @param string|null $callback
     *
     * @return $this
     */
    public function setCallback(?string $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Возвращает назначение платежа
     *
     * @return PaymentDestinationType
     */
    public function getDestination(): PaymentDestinationType
    {
        return $this->destination;
    }

    /**
     * Устанавливает назначение платежа
     *
     * @param PaymentDestinationType $destination
     *
     * @return $this
     */
    public function setDestination(PaymentDestinationType $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Возвращает флаг, является ли платеж рекуррентным
     *
     * @return RecurrentStatus
     */
    public function getRecurrentStatus(): RecurrentStatus
    {
        return $this->recurrent;
    }

    /**
     * Устанавливает флаг реккурености платежа
     *
     * @param RecurrentStatus $recurrent
     *
     * @return $this
     */
    public function setRecurrentStatus(RecurrentStatus $recurrent): self
    {
        $this->recurrent = $recurrent;

        return $this;
    }

    /**
     * Возвращает ID карты
     *
     * @return int|null
     */
    public function getCardId(): ?int
    {
        return $this->cardId;
    }

    /**
     * Устанавливает ID карты
     *
     * @param int|null $cardId
     *
     * @return $this
     */
    public function setCardId(?int $cardId): self
    {
        $this->cardId = $cardId;

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
     *
     * @return $this
     */
    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = new DateTimeImmutable();

        return $this;
    }

    /**
     * Возвращает дату обновления
     *
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Устанавливает дату обновления
     *
     * @return $this
     */
    #[ORM\PreUpdate]
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    /**
     * Возвращает дату удаления
     *
     * @return DateTimeInterface|null
     */
    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * Устанавливает дату удаления
     *
     * @param DateTimeInterface|null $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Возвращает подписку
     *
     * @return Subscription|null
     */
    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    /**
     * Устанавливает подписку
     *
     * @param Subscription $subscription
     *
     * @return $this
     */
    public function setSubscription(Subscription $subscription): self
    {
        // set the owning side of the relation if necessary
        if ($subscription->getPayment() !== $this) {
            $subscription->setPayment($this);
        }

        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Возвращает чек
     *
     * @return Check|null
     */
    public function getUserCheck(): ?Check
    {
        return $this->userCheck;
    }

    /**
     * Устанавливает чек
     *
     * @param Check $userCheck
     *
     * @return $this
     */
    public function setUserCheck(Check $userCheck): self
    {
        // set the owning side of the relation if necessary
        if ($userCheck->getPayment() !== $this) {
            $userCheck->setPayment($this);
        }

        $this->userCheck = $userCheck;

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
     */
    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return IncomingTransfer|null
     */
    public function getIncomingTransfer(): ?IncomingTransfer
    {
        return $this->incomingTransfer;
    }

    /**
     * @param IncomingTransfer|null $incomingTransfer
     *
     * @return $this
     */
    public function setIncomingTransfer(?IncomingTransfer $incomingTransfer): self
    {
        if ($incomingTransfer === null && $this->incomingTransfer !== null) {
            $this->incomingTransfer->setPayment(null);
        }

        if ($incomingTransfer !== null && $incomingTransfer->getPayment() !== $this) {
            $incomingTransfer->setPayment($this);
        }

        $this->incomingTransfer = $incomingTransfer;

        return $this;
    }
}
