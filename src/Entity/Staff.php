<?php

namespace App\Entity;

use App\Repository\StaffRepository;
use App\ValueObject\Phone;
use App\ValueObject\RecordActiveStatus;
use App\ValueObject\Role;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: StaffRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Staff implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * Логин
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $login;

    /**
     * Пароль
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $password;

    /**
     * Роли
     *
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Имя
     *
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    /**
     * Телефон
     *
     */
    #[ORM\Column(type: 'phone', nullable: true)]
    private ?Phone $phone = null;

    #[ORM\Column(type: 'recordActiveStatus')]
    private RecordActiveStatus $active;

    /**
     * Дата создания
     *
     * @var DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private ?DateTimeInterface $createdAt = null;

    /**
     * Посты, обработанные сотрудником
     *
     * @var Collection|null
     */
    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: Post::class, cascade: ['persist', 'remove'])]
    private ?Collection $posts;

    /**
     * Действия сотрудников
     *
     * @var Collection|null
     */
    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: StaffAction::class, cascade: ['persist', 'remove'])]
    private ?Collection $staffActions;

    /**
     * Комменты
     *
     * @var Collection|null
     */
    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: Comment::class)]
    private ?Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
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
     * @return RecordActiveStatus
     */
    public function getActive(): RecordActiveStatus
    {
        return $this->active;
    }

    /**
     * @param RecordActiveStatus $active
     */
    public function setActive(RecordActiveStatus $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     *
     * @return $this
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Возвращает массив ролей(каждая роль это ValueObject)
     *
     * @return Role[]
     */
    public function getRolesVO(): array
    {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = (new Role($role));
        }

        return $roles;
    }

    /**
     * Устанавливает роль
     *
     * @param Role $role
     *
     * @return $this
     */
    public function setRoles(Role $role): self
    {
        $this->roles = [$role->getValue()];

        return $this;
    }


    /**
     * @return void
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    /**
     * Возвращает имя сотрудника
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает имя сотрудника
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Возвращает телефон сотрудника
     *
     * @return Phone|null
     */
    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    /**
     * Устанавливает телефон сотрудника
     *
     * @param ?Phone $phone
     */
    public function setPhone(?Phone $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setStaff($this);
        }

        return $this;
    }

    /**
     * Есть ли у сотрудника роль "Админ"
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        foreach ($this->getRolesVO() as $role) {
            if ($role->isAdmin()) {
                return true;
            }
        }

        return false;
    }
}
