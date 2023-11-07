<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getOrder', 'getUser'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getOrder', 'getUser'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['getOrder', 'getUser'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getUser'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getUser'])]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['getUser'])]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getUser'])]
    private ?string $gender = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getUser'])]
    private ?string $address = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Order::class)]
    private Collection $clientOrders;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: Order::class)]
    private Collection $employeeOrders;

    public function __construct()
    {
        $this->clientOrders = new ArrayCollection();
        $this->employeeOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
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

    public function setRoles(array $roles): static
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getClientOrders(): Collection
    {
        return $this->clientOrders;
    }

    public function addClientOrder(Order $clientOrder): static
    {
        if (!$this->clientOrders->contains($clientOrder)) {
            $this->clientOrders->add($clientOrder);
            $clientOrder->setClient($this);
        }

        return $this;
    }

    public function removeClientOrder(Order $clientOrder): static
    {
        if ($this->clientOrders->removeElement($clientOrder)) {
            // set the owning side to null (unless already changed)
            if ($clientOrder->getClient() === $this) {
                $clientOrder->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getEmployeeOrders(): Collection
    {
        return $this->employeeOrders;
    }

    public function addEmployeeOrder(Order $employeeOrder): static
    {
        if (!$this->employeeOrders->contains($employeeOrder)) {
            $this->employeeOrders->add($employeeOrder);
            $employeeOrder->setEmployee($this);
        }

        return $this;
    }

    public function removeEmployeeOrder(Order $employeeOrder): static
    {
        if ($this->employeeOrders->removeElement($employeeOrder)) {
            // set the owning side to null (unless already changed)
            if ($employeeOrder->getEmployee() === $this) {
                $employeeOrder->setEmployee(null);
            }
        }

        return $this;
    }
}
