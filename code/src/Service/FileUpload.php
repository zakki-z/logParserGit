<?php

namespace App\Service;

use App\Entity\FileInfo;
use App\Entity\User;
use App\Repository\LogEntriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload
{
    private string $uploadDirectory;

    public function __construct(
        string $uploadDirectory
    )
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->ensureDirectoryExists();
    }
    public function upload(UploadedFile $file, string $filename): string
    {
        $file->move($this->uploadDirectory, $filename);
        return $this->uploadDirectory . '/' . $filename;
    }
    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0777, true);
        }
    }
}
