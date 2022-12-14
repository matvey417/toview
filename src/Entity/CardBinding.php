<?php

namespace App\Entity;

use App\Repository\CardBindingRepository;
use App\ValueObject\CardBindingStatus;
use App\ValueObject\CardNumber;
use App\ValueObject\CardSystemType;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность привязки карты
 */
#[ORM\Entity(repositoryClass: CardBindingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CardBinding
{
    /** Максимальное разрешенное количество успешных привязок за день */
    public const MAXIMUM_COUNT_BIND_CARD_PER_DAY = 3;

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
     * Пользователь
     *
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cardBindings')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * Merchant
     *
     * @var Merchant
     */
    #[ORM\ManyToOne(targetEntity: Merchant::class)]
    private Merchant $merch;

    /**
     * CardBindingSystem
     *
     * @var CardSystemType
     */
    #[ORM\Column(type: 'cardSystemType')]
    private CardSystemType $cardBindingSystem;

    /**
     * SystemId
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $systemId;

    /**
     * Статус
     *
     * @var CardBindingStatus
     */
    #[ORM\Column(type: 'cardBindingStatus')]
    private CardBindingStatus $status;

    /**
     * Номер карты
     *
     * @var CardNumber
     */
    #[ORM\Column(type: 'cardNumber', nullable: true)]
    private CardNumber $cardNumber;

    /**
     * Ответ запроса
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private string $jsonData;

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
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Возвращает Merchant
     *
     * @return Merchant|null
     */
    public function getMerch(): ?Merchant
    {
        return $this->merch;
    }

    /**
     * Устанавливает Merchant
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
     * Возвращает CardSystemType
     *
     * @return CardSystemType
     */
    public function getCardBindingSystem(): CardSystemType
    {
        return $this->cardBindingSystem;
    }

    /**
     * Устанавливает CardSystemType
     *
     * @param CardSystemType|null $cardBindingSystem
     *
     * @return $this
     */
    public function setCardBindingSystem(?CardSystemType $cardBindingSystem): self
    {
        $this->cardBindingSystem = $cardBindingSystem;

        return $this;
    }

    /**
     * Возвращает systemId
     *
     * @return string|null
     */
    public function getSystemId(): ?string
    {
        return $this->systemId;
    }

    /**
     * Устанавливает systemId
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
     * Возвращает статус
     *
     * @return CardBindingStatus
     */
    public function getStatus(): CardBindingStatus
    {
        return $this->status;
    }

    /**
     * Устанавливает статус
     *
     * @param CardBindingStatus|null $status
     *
     * @return $this
     */
    public function setStatus(?CardBindingStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Возвращает ответ запроса
     *
     * @return string|null
     */
    public function getJsonData(): ?string
    {
        return $this->jsonData;
    }

    /**
     * Устанавливает ответ запроса
     *
     * @param string|null $jsonData
     *
     * @return $this
     */
    public function setJsonData(?string $jsonData): self
    {
        $this->jsonData = $jsonData;

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
     * Возвращает номер карты
     *
     * @return CardNumber
     */
    public function getCardNumber(): CardNumber
    {
        return $this->cardNumber;
    }

    /**
     * Устанавливает номер карты
     *
     * @param CardNumber $cardNumber
     *
     * @return CardBinding
     */
    public function setCardNumber(CardNumber $cardNumber): self
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }
}
