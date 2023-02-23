<?php

namespace App\Entity;

use App\Entity\Traits\TimeStampable;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name:"users")]
//Ca signifie qu'on aura des méthode de cycle de vie lors de la génération de l'entité (ex: la fonction updateTimeStampable)
//Ne pas oublier de mettre de prepersist et update sur la methode en question lors de la mise a jour de l'entité
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    private ?string $email = null; 

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Pin::class, orphanRemoval: true)]
    private Collection $pins;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    private string $fullName;

    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: Pin::class)]
    // private Collection $pins;


    public function __construct()
    {
        $this->pins = new ArrayCollection();
    }

    use TimeStampable;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        // C'est l'info qui s'affiche au niveau du profiler lorsque qu'on est identifier
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName() : ?string
    {
        return $this->lastName." - ".$this->firstName;
    }

    // /**
    //  * @return Collection<int, Pin>
    //  */
    // public function getPins(): Collection
    // {
    //     return $this->pins;
    // }

    // public function addPin(Pin $pin): self
    // {
    //     if (!$this->pins->contains($pin)) {
    //         $this->pins->add($pin);
    //         $pin->setUser($this);
    //     }

    //     return $this;
    // }

    // public function removePin(Pin $pin): self
    // {
    //     if ($this->pins->removeElement($pin)) {
    //         // set the owning side to null (unless already changed)
    //         if ($pin->getUser() === $this) {
    //             $pin->setUser(null);
    //         }
    //     }

    //     return $this;
    // }

    /**
     * @return Collection<int, Pin>
     */
    public function getPins(): Collection
    {
        return $this->pins;
    }

    /**
     * Dans cette fonction on verifie avec le if si le pin n'est pas deja present 
     * si c'est pas le cas on le rajoute avec le add()
     * et on l'associe à l'utilisateur courant avec le setUser() en mettant le $this parce que c'est
     * l 'utiilisateur de la class courant
    */
    public function addPin(Pin $pin): self
    {
        if (!$this->pins->contains($pin)) {
            $this->pins->add($pin);
            $pin->setUser($this);
        }

        return $this;
    }

    /**
     * Dans cette fonction on supprime le pin puis l'utlilisateur qui posséde ce pin, car on 
     * a dit que les pins devait avoir un utilisateur
     *
     * @param Pin $pin
     * @return self
     */
    public function removePin(Pin $pin): self
    {
        if ($this->pins->removeElement($pin)) {
            // set the owning side to null (unless already changed)
            if ($pin->getUser() === $this) {
                $pin->setUser(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
