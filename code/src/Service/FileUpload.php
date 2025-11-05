<?php

namespace App\Service;

use App\Entity\FileInfo;
use App\Entity\User;
use App\Repository\LogEntriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

class FileUpload
{
    private string $uploadDirectory;

    public function __construct(
        string $uploadDirectory,
    )
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->ensureDirectoryExists($this->uploadDirectory);
    }

    public function upload(UploadedFile $file, string $filename): string
    {
        $file->move($this->uploadDirectory, $filename);
        return $this->uploadDirectory . '/' . $filename;
    }
    private function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
