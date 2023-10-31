<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $payment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $deposit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $pickUp = null;

    #[ORM\Column]
    private array $content = [];

    #[ORM\Column]
    private ?int $totalPrice = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'clientOrders')]
    private ?user $client = null;

    #[ORM\ManyToOne(inversedBy: 'employeeOrders')]
    private ?user $employee = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPayment(): ?\DateTimeInterface
    {
        return $this->payment;
    }

    public function setPayment(\DateTimeInterface $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getDeposit(): ?\DateTimeInterface
    {
        return $this->deposit;
    }

    public function setDeposit(\DateTimeInterface $deposit): static
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function getPickUp(): ?\DateTimeInterface
    {
        return $this->pickUp;
    }

    public function setPickUp(\DateTimeInterface $pickUp): static
    {
        $this->pickUp = $pickUp;

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getTotalPrice(): ?int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getClient(): ?user
    {
        return $this->client;
    }

    public function setClient(?user $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getEmployee(): ?user
    {
        return $this->employee;
    }

    public function setEmployee(?user $employee): static
    {
        $this->employee = $employee;

        return $this;
    }
}
