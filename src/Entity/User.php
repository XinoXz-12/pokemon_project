<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'trainer', cascade: ['persist', 'remove'])]
    private ?Pokedex $pokedex = null;

    /**
     * @var Collection<int, Battle>
     */
    #[ORM\OneToMany(targetEntity: Battle::class, mappedBy: 'trainer')]
    private Collection $battles;

    /**
     * @var Collection<int, Multibattle>
     */
    #[ORM\OneToMany(targetEntity: Multibattle::class, mappedBy: 'ally_trainer')]
    private Collection $multibattles;

    #[ORM\Column]
    private ?bool $open_battle = false;

    public function __construct()
    {
        $this->battles = new ArrayCollection();
        $this->multibattles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPokedex(): ?Pokedex
    {
        return $this->pokedex;
    }

    public function setPokedex(Pokedex $pokedex): static
    {
        // set the owning side of the relation if necessary
        if ($pokedex->getTrainer() !== $this) {
            $pokedex->setTrainer($this);
        }

        $this->pokedex = $pokedex;

        return $this;
    }

    /**
     * @return Collection<int, Battle>
     */
    public function getBattles(): Collection
    {
        return $this->battles;
    }

    public function addBattle(Battle $battle): static
    {
        if (!$this->battles->contains($battle)) {
            $this->battles->add($battle);
            $battle->setTrainer($this);
        }

        return $this;
    }

    public function removeBattle(Battle $battle): static
    {
        if ($this->battles->removeElement($battle)) {
            // set the owning side to null (unless already changed)
            if ($battle->getTrainer() === $this) {
                $battle->setTrainer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Multibattle>
     */
    public function getMultibattles(): Collection
    {
        return $this->multibattles;
    }

    public function addMultibattle(Multibattle $multibattle): static
    {
        if (!$this->multibattles->contains($multibattle)) {
            $this->multibattles->add($multibattle);
            $multibattle->setAllyTrainer($this);
        }

        return $this;
    }

    public function removeMultibattle(Multibattle $multibattle): static
    {
        if ($this->multibattles->removeElement($multibattle)) {
            // set the owning side to null (unless already changed)
            if ($multibattle->getAllyTrainer() === $this) {
                $multibattle->setAllyTrainer(null);
            }
        }

        return $this;
    }

    public function isOpenBattle(): ?bool
    {
        return $this->open_battle;
    }

    public function setOpenBattle(bool $open_battle): static
    {
        $this->open_battle = $open_battle;

        return $this;
    }
}
