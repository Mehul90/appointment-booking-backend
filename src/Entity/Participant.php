<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['email'], message: 'This participant is already exist.')]
#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['api'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['api'])]
    private ?string $email = null;

    #[ORM\Column(length: 15)]
    #[Groups(['api'])]
    private ?string $phone = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['api'])]
    private ?string $color = null;

    #[ORM\OneToMany(targetEntity: AppointmentParticipant::class, mappedBy: 'participant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['api'])]
    private Collection $appointmentParticipants;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->appointmentParticipants = new ArrayCollection();
    }

    /**
     * @return Collection<int, AppointmentParticipant>
     */
    public function getAppointmentParticipants(): Collection
    {
        return $this->appointmentParticipants;
    }

    public function addAppointmentParticipant(AppointmentParticipant $appointmentParticipant): static
    {
        if (!$this->appointmentParticipants->contains($appointmentParticipant)) {
            $this->appointmentParticipants[] = $appointmentParticipant;
            $appointmentParticipant->setParticipant($this);
        }

        return $this;
    }

    public function removeAppointmentParticipant(AppointmentParticipant $appointmentParticipant): static
    {
        if ($this->appointmentParticipants->removeElement($appointmentParticipant)) {
            if ($appointmentParticipant->getParticipant() === $this) {
                $appointmentParticipant->setParticipant(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }
} 
