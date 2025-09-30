<?php

namespace App\Entity;

use App\Repository\FileInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;


#[ORM\Entity(repositoryClass: FileInfoRepository::class)]
class FileInfo
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private ?Uuid $id = null;


    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(length: 255)]
    private ?string $fileNameTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column]
    private ?int $fileSize = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY', inversedBy: 'file')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user= null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }
    public function getFileNameTime(): ?string
    {
        return $this->fileNameTime;
    }
    public function setFileNameTime(string $fileNameTime): static
    {
        $this->fileNameTime = $fileNameTime;
        return $this;
    }
    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploaded_at): static
    {
        $this->uploadedAt = $uploaded_at;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $file_size): static
    {
        $this->fileSize = $file_size;

        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
