<?php

namespace App\Entity;

use App\Repository\LogEntriesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogEntriesRepository::class)]
class LogEntries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $channel = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: types::TEXT)]
    private ?string $information = null;

    #[ORM\ManyToOne(targetEntity: FileInfo::class, fetch: 'LAZY', inversedBy: 'logEntries')]
    #[ORM\JoinColumn(nullable: true)]
    private ?FileInfo $file = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(string $information): static
    {
        $this->information = $information;

        return $this;
    }
    public function getFile(): ?FileInfo
    {
        return $this->file;
    }
    public function setFile(?FileInfo $file): static
    {
        $this->file = $file;
        return $this;
    }
}
