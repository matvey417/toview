<?php

namespace App\Entity;

use App\Enum\RecordActiveStatusEnum;
use App\Repository\CardRepository;
use App\ValueObject\BinCard;
use App\ValueObject\CardSystemType;
use App\ValueObject\PanCard;
use App\ValueObject\RecordActiveStatus;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность карты
 */
#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Card
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
     * Пользователь
     *
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cards')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * SystemId
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $systemId;

    /**
     * BindingSystem
     *
     * @var CardSystemType
     */
    #[ORM\Column(type: 'cardSystemType', nullable: true)]
    private CardSystemType $bindingSystem;

    /**
     * Первая часть карты
     *
     * @var BinCard
     */
    #[ORM\Column(type: 'binCard', length: 6, nullable: true)]
    private BinCard $bin;

    /**
     * Последняя часть карты
     *
     * @var PanCard
     */
    #[ORM\Column(type: 'panCard', length: 4, nullable: true)]
    private PanCard $pan;

    /**
     * Имя держателя карты
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 55, nullable: true)]
    private string $holderName;

    /**
     * Срок действия
     *
     * @var DateTimeInterface
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTimeInterface $expirationDate;

    /**
     * Флаг активности карты
     *
     * @var RecordActiveStatus|null
     */
    #[ORM\Column(type: 'recordActiveStatus', nullable: false, options: ["default" => 0])]
    private ?RecordActiveStatus $active;

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
     * Подписки
     *
     * @var Collection|null
     */
    #[ORM\OneToMany(mappedBy: 'card', targetEntity: Subscription::class)]
    private ?Collection $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
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
     * Возвращает bindingSystem
     *
     * @return CardSystemType
     */
    public function getBindingSystem(): CardSystemType
    {
        return $this->bindingSystem;
    }

    /**
     * Устанавливает bindingSystem
     *
     * @param $bindingSystem
     *
     * @return $this
     */
    public function setBindingSystem($bindingSystem): self
    {
        $this->bindingSystem = $bindingSystem;

        return $this;
    }

    /**
     * Возвращает первую часть номера карты (6 цифр)
     *
     * @return BinCard
     */
    public function getBin(): BinCard
    {
        return $this->bin;
    }

    /**
     * Устанавливает первую часть номера карты
     *
     * @param BinCard|null $bin
     *
     * @return $this
     */
    public function setBin(?BinCard $bin): self
    {
        $this->bin = $bin;

        return $this;
    }

    /**
     * Возвращает последнюю часть номера карты (4 цифры)
     *
     * @return PanCard
     */
    public function getPan(): PanCard
    {
        return $this->pan;
    }

    /**
     * Устанавливает последнюю часть номера карты
     *
     * @param PanCard|null $pan
     *
     * @return $this
     */
    public function setPan(?PanCard $pan): self
    {
        $this->pan = $pan;

        return $this;
    }

    /**
     * Возвращает имя держателя карты
     *
     * @return string|null
     */
    public function getHolderName(): ?string
    {
        return $this->holderName;
    }

    /**
     * Устанавливает имя держателя карты
     *
     * @param string|null $holderName
     *
     * @return $this
     */
    public function setHolderName(?string $holderName): self
    {
        $this->holderName = $holderName;

        return $this;
    }

    /**
     * Возвращает срок действия
     *
     * @return DateTimeInterface|null
     */
    public function getExpirationDate(): ?DateTimeInterface
    {
        return $this->expirationDate;
    }

    /**
     * Устанавливает срок действия
     *
     * @param DateTimeInterface|null $expirationDate
     *
     * @return $this
     */
    public function setExpirationDate(?DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

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
     * Если карта актуальна возвращает true
     * актуальной карта считается если DeletedAt = null и active = 1
     *
     * @return bool
     */
    public function isActual(): bool
    {
        return null === $this->getDeletedAt() && $this->getActive()->isActive();
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
     * Устанавливает дату обновления
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
     * Возвращает VO флага активности карты
     *
     * @return RecordActiveStatus|null
     */
    public function getActive(): ?RecordActiveStatus
    {
        return $this->active;
    }

    /**
     * Возвращает VO флага активности карты
     *
     * @param RecordActiveStatus|null $active
     *
     * @return Card
     */
    public function setActive(?RecordActiveStatus $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Удалить карту(устанавливаем active = 0 и deletedAt)
     *
     * @return void
     */
    public function remove(): void
    {
        $this->setDeletedAt(new DateTimeImmutable());
        $this->setActive(new RecordActiveStatus(RecordActiveStatusEnum::Disabled->value));
    }

    /**
     * Возвращает подписки
     *
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    /**
     * Добавляет подписку
     *
     * @param Subscription $subscription
     *
     * @return $this
     */
    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setCard($this);
        }

        return $this;
    }

    /**
     * Удаляет подписку
     *
     * @param Subscription $subscription
     *
     * @return $this
     */
    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getCard() === $this) {
                $subscription->setCard(null);
            }
        }

        return $this;
    }

    /**
     * Возвращает номер карты
     *
     * @return string
     */
    protected function getCardNumber(): string
    {
        return $this->bin->getValue() . 'XXXXXX' . $this->pan->getValue();
    }

    /**
     * Возвращает форматированный номер карты(4569 45XX XXXX 9132)
     *
     * @return string
     */
    public function getFormattedCardNumber(): string
    {
        $number = '';

        if (!empty($this->bin) && !empty($this->pan)) {
            $number = trim(preg_replace('/(.{4})/', '$1 ', $this->getCardNumber()));
        }

        return $number;
    }
}
