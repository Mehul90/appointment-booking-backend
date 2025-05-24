<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\AppointmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['api'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api'])]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Groups(['api'])]
    private ?string $color = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank]
    #[Groups(['api'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'time_immutable')]
    #[Assert\NotBlank]
    #[Groups(['api'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'time_immutable')]
    #[Assert\NotBlank]
    #[Assert\Expression(
        "this.getStartTime() < this.getEndTime()",
        message: "End time must be after start time"
    )]
    #[Groups(['api'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\OneToMany(mappedBy: 'appointment', targetEntity: AppointmentParticipant::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['appointment:read', 'appointment:participants'])]
    private Collection $appointmentParticipants;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['appointment:read', 'appointment:user'])]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->appointmentParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getStartTime(): string
    {
        if ($this->startTime instanceof \DateTimeInterface) {
            return  $this->startTime->format("H:i");
        }
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): string
    {
        if ($this->endTime instanceof \DateTimeInterface) {
            return  $this->endTime->format("H:i");
        }

        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getAppointmentParticipants(): Collection
    {
        return $this->appointmentParticipants;
    }

    public function addAppointmentParticipant(AppointmentParticipant $appointmentParticipant): static
    {
        if (!$this->appointmentParticipants->contains($appointmentParticipant)) {
            $this->appointmentParticipants[] = $appointmentParticipant;
            $appointmentParticipant->setAppointment($this);
        }

        return $this;
    }

    public function removeAppointmentParticipant(AppointmentParticipant $appointmentParticipant): static
    {
        if ($this->appointmentParticipants->removeElement($appointmentParticipant)) {
            // set the owning side to null (unless already changed)
            if ($appointmentParticipant->getAppointment() === $this) {
                $appointmentParticipant->setAppointment(null);
            }
        }

        return $this;
    }

    public function getDate(): string
    {
        if ($this->date instanceof \DateTimeInterface) {
            return  $this->date->format("Y-m-d");
        }

        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $user): static
    {
        $this->createdBy = $user;
        return $this;
    }
} 
